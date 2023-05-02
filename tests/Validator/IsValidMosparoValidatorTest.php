<?php

/**
 * @package   MosparoBundle
 * @author    Arnaud RITTI <arnaud.ritti@gmail.com>
 * @copyright 2023 Arnaud RITTI
 * @license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
 * @link      https://github.com/arnaud-ritti/mosparo-bundle
 */

declare(strict_types=1);

namespace Mosparo\MosparoBundle\Tests\Validator;

use Mosparo\ApiClient\VerificationResult;
use Mosparo\MosparoBundle\Form\Type\MosparoType;
use Mosparo\MosparoBundle\Serializer\FormNormalizer;
use Mosparo\MosparoBundle\Services\MosparoClient;
use Mosparo\MosparoBundle\Tests\Traits\FormTrait;
use Mosparo\MosparoBundle\Validator\IsValidMosparo;
use Mosparo\MosparoBundle\Validator\IsValidMosparoValidator;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<IsValidMosparoValidator>
 *
 * @internal
 */
class IsValidMosparoValidatorTest extends ConstraintValidatorTestCase
{
    use FormTrait;

    public const INSTANCE_URL = 'http://test.local';
    public const PUBLIC_KEY = 'testPublicKey';
    public const PRIVATE_KEY = 'testPrivateKey';
    public const SUBMIT_TOKEN = 'submitToken';
    public const VALIDATION_TOKEN = 'validationToken';

    protected function setUp(): void
    {
        $this->setUpForm();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->tearDownForm();
    }

    protected function makeValidator(
        bool $submittable = false,
        bool $valid = false,
        array $verifiedFields = [],
        array $issues = [],
        ?string $submitToken = self::SUBMIT_TOKEN,
        ?string $validationToken = self::VALIDATION_TOKEN,
        ?bool $enabled = true
    ): ConstraintValidatorInterface|InvocationMocker {
        $requestStack = $this->createMock(RequestStack::class);
        $request = $this->createMock(Request::class);
        $request->method('get')
            ->willReturnCallback(
                function ($query) use ($submitToken, $validationToken) {
                    return match ($query) {
                        '_mosparo_submitToken' => $submitToken,
                        '_mosparo_validationToken' => $validationToken,
                    };
                }
            )
        ;
        $requestStack->method('getMainRequest')->willReturn($request);

        $client = $this->getMockBuilder(MosparoClient::class)
            ->setConstructorArgs([self::INSTANCE_URL, self::PUBLIC_KEY, self::PRIVATE_KEY])
            ->getMock()
        ;

        $client->method('verifySubmission')->willReturn(
            new VerificationResult(
                $submittable,
                $valid,
                $verifiedFields,
                $issues
            )
        );

        $normalizer = new FormNormalizer($this->dispatcher);

        $validator = $this->getMockBuilder(IsValidMosparoValidator::class)
            ->setConstructorArgs([$requestStack, $this->configuration->getParameterBag(), $normalizer, $enabled])
            ->onlyMethods(['getClient'])
            ->getMock()
        ;

        $validator->method('getClient')->willReturn($client);

        return $validator;
    }

    protected function setValidator(
        bool $submittable = false,
        bool $valid = false,
        array $verifiedFields = [],
        array $issues = [],
        ?string $submitToken = self::SUBMIT_TOKEN,
        ?string $validationToken = self::VALIDATION_TOKEN,
        ?bool $enabled = true
    ): void {
        $this->context = $this->createContext();
        $this->validator = $this->makeValidator($submittable, $valid, $verifiedFields, $issues, $submitToken, $validationToken, $enabled);
        $this->validator->initialize($this->context);
    }

    protected function createValidator(): ConstraintValidatorInterface|InvocationMocker
    {
        return $this->makeValidator();
    }

    public function testReturnClient(): void
    {
        $submitToken = self::SUBMIT_TOKEN;
        $validationToken = self::VALIDATION_TOKEN;
        $requestStack = $this->createMock(RequestStack::class);
        $request = $this->createMock(Request::class);
        $request->method('get')
            ->willReturnCallback(
                function ($query) use ($submitToken, $validationToken) {
                    return match ($query) {
                        '_mosparo_submitToken' => $submitToken,
                        '_mosparo_validationToken' => $validationToken,
                    };
                }
            )
        ;
        $requestStack->method('getMainRequest')->willReturn($request);
        $normalizer = new FormNormalizer($this->dispatcher);
        $validator = new IsValidMosparoValidator($requestStack, $this->configuration->getParameterBag(), $normalizer);
        self::assertInstanceOf(MosparoClient::class, $validator->getClient());
    }

    public function testIsValid(): void
    {
        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('mosparo', MosparoType::class)
        ;

        $this->setPropertyPath('mosparo');

        $form->submit(['name' => 'John Example', 'submit' => '']);
        $this->setRoot($form);
        $this->setValidator(
            true,
            true,
            [
                'name[name]' => VerificationResult::FIELD_VALID,
            ]
        );

        $this->validator->validate(null, new IsValidMosparo());
        $this->assertNoViolation();
    }

    public function testValidIfNotEnabled(): void
    {
        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('mosparo', MosparoType::class)
        ;

        $this->setPropertyPath('mosparo');

        $form->submit(['name' => 'John Example', 'submit' => '']);
        $this->setRoot($form);
        $this->setValidator(
            false,
            false,
            [
                'name[name]' => VerificationResult::FIELD_INVALID,
            ],
            [],
            self::SUBMIT_TOKEN,
            self::VALIDATION_TOKEN,
            false
        );

        $this->validator->validate(null, new IsValidMosparo());
        $this->assertNoViolation();
    }

    public function testIsInvalid(): void
    {
        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('mosparo', MosparoType::class)
        ;

        $this->setPropertyPath('mosparo');

        $form->submit(['submit' => '']);
        $this->setRoot($form);
        $this->setValidator(
            false,
            false,
            [
                'name[name]' => VerificationResult::FIELD_INVALID,
            ],
            [
                ['name' => 'name[name]', 'message' => 'Missing in form data, verification not possible.'],
            ]
        );

        $this->validator->validate(null, new IsValidMosparo());
        $this->buildViolation('Missing in form data, verification not possible.')->atPath('mosparo')->assertRaised();
    }

    public function testIsNested(): void
    {
        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add(
                'collection',
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                ]
            )
            ->add('mosparo', MosparoType::class)
        ;

        $this->setPropertyPath('mosparo');

        $form->submit(
            [
                'name' => 'John Example',
                'collection' => [
                    0 => 'Entry 1',
                    1 => 'Entry 2',
                ],
                'submit' => '',
            ]
        );
        $this->setRoot($form);
        $this->setValidator(
            true,
            true,
            [
                'name[name]' => VerificationResult::FIELD_VALID,
                'name[collection][0]' => VerificationResult::FIELD_VALID,
                'name[collection][1]' => VerificationResult::FIELD_VALID,
            ]
        );
        $this->validator->validate(null, new IsValidMosparo());
        $this->assertNoViolation();
    }

    public function testWrongConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(null, new Form());
    }

    public function testOutsideForm(): void
    {
        $this->setRoot('root');
        $this->validator->validate(null, new IsValidMosparo());
        $this->buildViolation(IsValidMosparo::ERROR)->assertRaised();
    }

    public function testWithoutTokens(): void
    {
        $form = $this->getCompoundForm([])
            ->add('name', TextType::class)
            ->add('mosparo', MosparoType::class)
        ;

        $this->setPropertyPath('mosparo');

        $form->submit(['name' => 'John Example', 'submit' => '']);
        $this->setRoot($form);
        $this->setValidator(
            true,
            true,
            [
                'name[name]' => VerificationResult::FIELD_VALID,
            ],
            [],
            null,
            null
        );

        $this->validator->validate(null, new IsValidMosparo());
        $this->buildViolation(IsValidMosparo::INVALID_TOKEN)->atPath('mosparo')->assertRaised();
    }
}
