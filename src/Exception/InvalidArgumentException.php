<?php

declare(strict_types=1);

namespace Atelier\Pattern\Exception;

/**
 * Thrown when a pattern receives geometry or style values it cannot tile with.
 */
final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
