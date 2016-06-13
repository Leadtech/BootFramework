<?php
namespace Boot\Http\Service\Handler;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RequestHandlerInterface
 * @package Boot\Http\Service\Handler
 */
interface RequestHandlerInterface
{
    /**
     * Dispatch service
     *
     * @param Request|null $request
     * @return void
     */
    public function handle(Request $request = null);
}