<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Workflow extends BaseConfig
{
    /**
     * Permite activar o desactivar globalmente
     * el Dynamic Workflow Engine.
     */
    public bool $dynamicEngineEnabled = false;

    /**
     * Workflow principal para atención ciudadana.
     */
    public string $defaultWorkflowSlug = 'citizen-attention';

    /**
     * Si el motor dinámico falla, permite regresar
     * automáticamente al Flow Engine clásico.
     */
    public bool $fallbackToLegacyFlow = true;
}