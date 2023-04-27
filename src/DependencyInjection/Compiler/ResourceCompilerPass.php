<?php

namespace Mosparo\MosparoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResourceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter(
            'twig.form.resources',
            array_merge(
                [
                    '@Mosparo/mosparo_widget.html.twig',
                ],
                $container->getParameter('twig.form.resources')
            )
        );
    }
}
