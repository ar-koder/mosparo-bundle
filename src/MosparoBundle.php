<?php

namespace Mosparo\MosparoBundle;

use Mosparo\MosparoBundle\DependencyInjection\Compiler\ResourceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MosparoBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ResourceCompilerPass());
    }
}
