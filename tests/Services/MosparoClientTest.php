<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests\Services;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mosparo\ApiClient\Exception;
use Mosparo\ApiClient\RequestHelper;
use Mosparo\ApiClient\VerificationResult;
use Mosparo\MosparoBundle\Services\MosparoClient;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MosparoClientTest extends TestCase
{
    public const INSTANCE_URL = 'http://test.local';
    public const PUBLIC_KEY = 'testPublicKey';
    public const PRIVATE_KEY = 'testPrivateKey';
    public const SUBMIT_TOKEN = 'submitToken';
    public const VALIDATION_TOKEN = 'validationToken';

    protected MockHandler $handler;

    protected HandlerStack $handlerStack;

    protected array $history = [];

    protected function setUp(): void
    {
        $historyMiddleware = Middleware::history($this->history);

        $this->handler = new MockHandler();

        $this->handlerStack = HandlerStack::create($this->handler);
        $this->handlerStack->push($historyMiddleware);
    }

    public function testSingletonInstance(): void
    {
        $apiClient = MosparoClient::make(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY);
        self::assertInstanceOf(MosparoClient::class, $apiClient);
    }

    public function testSingletonBadHostInstance(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please check yours "instance_url". "example" is not a valid URL');

        MosparoClient::make('example', self::PUBLIC_KEY, self::PRIVATE_KEY);
    }

    public function testVerifySubmissionWithoutTokens(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Submit or validation token not available.');

        $apiClient = new MosparoClient(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY, ['handler' => $this->handlerStack]);
        $apiClient->verifySubmission(['name' => 'John Example']);
    }

    public function testVerifySubmissionWithoutValidationTokens(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Submit or validation token not available.');

        $apiClient = new MosparoClient(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY, ['handler' => $this->handlerStack]);
        $apiClient->verifySubmission(['name' => 'John Example', '_mosparo_submitToken' => self::SUBMIT_TOKEN]);
    }

    public function testVerifySubmissionFormTokensEmptyResponse(): void
    {
        $this->handler->append(new Response(200, ['Content-Type' => 'application/json'], json_encode([], \JSON_THROW_ON_ERROR)));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Response from API invalid.');

        $apiClient = new MosparoClient(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY, ['handler' => $this->handlerStack]);
        $apiClient->verifySubmission(['name' => 'John Example', '_mosparo_submitToken' => self::SUBMIT_TOKEN, '_mosparo_validationToken' => self::VALIDATION_TOKEN]);
    }

    public function testVerifySubmissionTokensAsArgumentEmptyResponse(): void
    {
        $this->handler->append(new Response(200, ['Content-Type' => 'application/json'], json_encode([], \JSON_THROW_ON_ERROR)));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Response from API invalid.');

        $apiClient = new MosparoClient(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY, ['handler' => $this->handlerStack]);
        $apiClient->verifySubmission(['name' => 'John Example'], self::SUBMIT_TOKEN, self::VALIDATION_TOKEN);
    }

    public function testVerifySubmissionConnectionError(): void
    {
        $this->handler->append(new RequestException('Error Communicating with Server', new Request('GET', 'test')));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('An error occurred while sending the request to mosparo.');

        $apiClient = new MosparoClient(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY, ['handler' => $this->handlerStack]);
        $apiClient->verifySubmission(['name' => 'John Example'], self::SUBMIT_TOKEN, self::VALIDATION_TOKEN);
    }

    /**
     * @throws \JsonException|Exception
     */
    public function testVerifySubmissionIsValid(): void
    {
        $formData = ['name' => 'John Example'];

        // Prepare the test data
        $requestHelper = new RequestHelper(self::PUBLIC_KEY, self::PRIVATE_KEY);

        $preparedFormData = $requestHelper->prepareFormData($formData);
        $formSignature = $requestHelper->createFormDataHmacHash($preparedFormData);

        $validationSignature = $requestHelper->createHmacHash(self::VALIDATION_TOKEN);
        $verificationSignature = $requestHelper->createHmacHash(sprintf('%s%s', $validationSignature, $formSignature));

        // Set the response
        $this->handler->append(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(
                    [
                        'valid' => true,
                        'verificationSignature' => $verificationSignature,
                        'verifiedFields' => ['name' => VerificationResult::FIELD_VALID],
                        'issues' => [],
                    ],
                    \JSON_THROW_ON_ERROR
                )
            )
        );

        // Start the test
        $apiClient = new MosparoClient(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY, ['handler' => $this->handlerStack]);

        $result = $apiClient->verifySubmission($formData, self::SUBMIT_TOKEN, self::VALIDATION_TOKEN);

        // Check the result
        self::assertInstanceOf(VerificationResult::class, $result);
        self::assertCount(1, $this->history);
        self::assertTrue($result->isSubmittable());
        self::assertTrue($result->isValid());
        self::assertEquals(VerificationResult::FIELD_VALID, $result->getVerifiedField('name'));
        self::assertFalse($result->hasIssues());

        $requestData = json_decode((string) $this->history[0]['request']->getBody(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertEquals($requestData['formData'], $preparedFormData);
        self::assertEquals(self::SUBMIT_TOKEN, $requestData['submitToken']);
        self::assertEquals($requestData['validationSignature'], $validationSignature);
        self::assertEquals($requestData['formSignature'], $formSignature);
    }

    /**
     * @throws \JsonException|Exception
     */
    public function testVerifySubmissionIsNotValid(): void
    {
        $formData = ['name' => 'John Example'];

        // Prepare the test data
        $requestHelper = new RequestHelper(self::PUBLIC_KEY, self::PRIVATE_KEY);

        $preparedFormData = $requestHelper->prepareFormData($formData);
        $formSignature = $requestHelper->createFormDataHmacHash($preparedFormData);

        $validationSignature = $requestHelper->createHmacHash(self::VALIDATION_TOKEN);

        // Set the response
        $this->handler->append(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(
                    [
                        'error' => true,
                        'errorMessage' => 'Validation failed.',
                    ],
                    \JSON_THROW_ON_ERROR
                )
            )
        );

        // Start the test
        $apiClient = new MosparoClient(self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY, ['handler' => $this->handlerStack]);

        $result = $apiClient->verifySubmission($formData, self::SUBMIT_TOKEN, self::VALIDATION_TOKEN);

        // Check the result
        self::assertInstanceOf(VerificationResult::class, $result);
        self::assertCount(1, $this->history);
        self::assertFalse($result->isSubmittable());
        self::assertFalse($result->isValid());
        self::assertTrue($result->hasIssues());
        self::assertEquals('Validation failed.', $result->getIssues()[0]['message']);

        $requestData = json_decode((string) $this->history[0]['request']->getBody(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertEquals($requestData['formData'], $preparedFormData);
        self::assertEquals(self::SUBMIT_TOKEN, $requestData['submitToken']);
        self::assertEquals($requestData['validationSignature'], $validationSignature);
        self::assertEquals($requestData['formSignature'], $formSignature);
    }
}
