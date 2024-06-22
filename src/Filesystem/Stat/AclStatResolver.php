<?php

/*
 * PHP Code Shift - Monkey-patch PHP code on the fly.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/php-code-shift/
 *
 * Released under the MIT license.
 * https://github.com/asmblah/php-code-shift/raw/main/MIT-LICENSE.txt
 */

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Filesystem\Stat;

use Asmblah\PhpCodeShift\Filesystem\Access\AccessResolverInterface;
use Asmblah\PhpCodeShift\Posix\PosixInterface;

/**
 * Class AclStatResolver.
 *
 * Emulates ACLs and other access control mechanisms within the Unix permission mode,
 * as stream wrappers cannot directly support ACLs.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AclStatResolver implements StatResolverInterface
{
    public function __construct(
        private readonly StatResolverInterface $statResolver,
        private readonly PosixInterface $posix,
        private readonly AccessResolverInterface $accessResolver
    ) {
    }

    /**
     * @inheritDoc
     */
    public function stat(string $path, bool $link, bool $quiet): array|null
    {
        $stat = $this->statResolver->stat($path, link: $link, quiet: $quiet);

        if ($stat === null) {
            return null;
        }

        /*
         * When using the native stream wrapper, is_writable(...) correctly handles ACLs.
         * However, when using a custom stream wrapper, only the mode in the returned stat is checked.
         * This means that if write permission is only granted by ACL for example, then as that cannot
         * be represented within the mode, is_writable(...) ends up returning false.
         *
         * We cannot directly change the return value of is_writable(...), but we can tweak the mode
         * in the returned stat to allow write permission.
         *
         * Similar to the native stream wrapper, we determine which of the Unix permission classes
         * (user, group or other) is most applicable and set its relevant bit,
         * because PHP will internally check the relevant one based on ownership of the file.
         *
         * Note that due to PHP's stat cache, if this file is stat'ed again before a different file,
         * the modified stat result (with tweaked Unix permissions mode) will be used.
         */
        $isExecutable = $this->accessResolver->isExecutable($path);
        $isReadable = $this->accessResolver->isReadable($path);
        $isWritable = $this->accessResolver->isWritable($path);

        if ($isExecutable || $isReadable || $isWritable) {
            // As explained above, tweak the mode accordingly to emulate the ACL within the Unix permission mode.
            $bitmask = 0;

            $grantBits = ($isExecutable ? 01 : 0) | ($isWritable ? 02 : 0) | ($isReadable ? 04 : 0);

            if ($this->posix->isPosixAvailable()) {
                if ($stat['uid'] === $this->posix->getUserId()) {
                    // Current process is running as the same user that owns the file, so tweak the user permission.
                    $bitmask = $grantBits << 6;
                } elseif (
                    $stat['gid'] === $this->posix->getGroupId() ||
                    in_array($stat['gid'], $this->posix->getGroupSet(), true)
                ) {
                    // Current process is running as either the same group that owns the file
                    // or one of the current process' user's groups, so tweak the group permission.
                    $bitmask = $grantBits << 3;
                }
            }

            if ($bitmask === 0) {
                // Use the "other" permission class otherwise.
                $bitmask = $grantBits;
            }

            $stat['mode'] |= $bitmask;
        }

        // If not accessible, then there is no reason to tweak the Unix permission mode.

        return $stat;
    }
}
