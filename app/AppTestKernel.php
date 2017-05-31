<?php

class AppTestKernel extends AppKernel
{
    /**
     * {@inheritdoc}
     */
    public function getLocalParams()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function isInstalled()
    {
        return true;
    }
}
