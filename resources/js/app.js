/*
 * Shared browser runtime entrypoint.
 *
 * The previous branch used admin.js as the shared Alpine/axios foundation.
 * Keep that implementation isolated behind the semantic app.js entry so
 * layouts no longer depend on an admin-named runtime.
 */

import './admin.js'
