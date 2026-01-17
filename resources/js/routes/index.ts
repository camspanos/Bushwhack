import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

login.form = loginForm

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

logout.form = logoutForm

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
* @route '/register'
*/
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ["get","head"],
    url: '/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
* @route '/register'
*/
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
* @route '/register'
*/
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
* @route '/register'
*/
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
})

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
* @route '/register'
*/
const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: register.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
* @route '/register'
*/
registerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: register.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
* @route '/register'
*/
registerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: register.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

register.form = registerForm

/**
* @see routes/web.php:13
* @route '/'
*/
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

home.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:13
* @route '/'
*/
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options)
}

/**
* @see routes/web.php:13
* @route '/'
*/
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

/**
* @see routes/web.php:13
* @route '/'
*/
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
})

/**
* @see routes/web.php:13
* @route '/'
*/
const homeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: home.url(options),
    method: 'get',
})

/**
* @see routes/web.php:13
* @route '/'
*/
homeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: home.url(options),
    method: 'get',
})

/**
* @see routes/web.php:13
* @route '/'
*/
homeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: home.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

home.form = homeForm

/**
* @see routes/web.php:19
* @route '/dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:19
* @route '/dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see routes/web.php:19
* @route '/dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see routes/web.php:19
* @route '/dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see routes/web.php:19
* @route '/dashboard'
*/
const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see routes/web.php:19
* @route '/dashboard'
*/
dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see routes/web.php:19
* @route '/dashboard'
*/
dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

dashboard.form = dashboardForm

/**
* @see routes/web.php:23
* @route '/fishing-log'
*/
export const fishingLog = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fishingLog.url(options),
    method: 'get',
})

fishingLog.definition = {
    methods: ["get","head"],
    url: '/fishing-log',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:23
* @route '/fishing-log'
*/
fishingLog.url = (options?: RouteQueryOptions) => {
    return fishingLog.definition.url + queryParams(options)
}

/**
* @see routes/web.php:23
* @route '/fishing-log'
*/
fishingLog.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fishingLog.url(options),
    method: 'get',
})

/**
* @see routes/web.php:23
* @route '/fishing-log'
*/
fishingLog.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: fishingLog.url(options),
    method: 'head',
})

/**
* @see routes/web.php:23
* @route '/fishing-log'
*/
const fishingLogForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fishingLog.url(options),
    method: 'get',
})

/**
* @see routes/web.php:23
* @route '/fishing-log'
*/
fishingLogForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fishingLog.url(options),
    method: 'get',
})

/**
* @see routes/web.php:23
* @route '/fishing-log'
*/
fishingLogForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fishingLog.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

fishingLog.form = fishingLogForm

/**
* @see routes/web.php:31
* @route '/locations-page'
*/
export const locationsPage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: locationsPage.url(options),
    method: 'get',
})

locationsPage.definition = {
    methods: ["get","head"],
    url: '/locations-page',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:31
* @route '/locations-page'
*/
locationsPage.url = (options?: RouteQueryOptions) => {
    return locationsPage.definition.url + queryParams(options)
}

/**
* @see routes/web.php:31
* @route '/locations-page'
*/
locationsPage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: locationsPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:31
* @route '/locations-page'
*/
locationsPage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: locationsPage.url(options),
    method: 'head',
})

/**
* @see routes/web.php:31
* @route '/locations-page'
*/
const locationsPageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: locationsPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:31
* @route '/locations-page'
*/
locationsPageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: locationsPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:31
* @route '/locations-page'
*/
locationsPageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: locationsPage.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

locationsPage.form = locationsPageForm

/**
* @see routes/web.php:35
* @route '/equipment-page'
*/
export const equipmentPage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: equipmentPage.url(options),
    method: 'get',
})

equipmentPage.definition = {
    methods: ["get","head"],
    url: '/equipment-page',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:35
* @route '/equipment-page'
*/
equipmentPage.url = (options?: RouteQueryOptions) => {
    return equipmentPage.definition.url + queryParams(options)
}

/**
* @see routes/web.php:35
* @route '/equipment-page'
*/
equipmentPage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: equipmentPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:35
* @route '/equipment-page'
*/
equipmentPage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: equipmentPage.url(options),
    method: 'head',
})

/**
* @see routes/web.php:35
* @route '/equipment-page'
*/
const equipmentPageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: equipmentPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:35
* @route '/equipment-page'
*/
equipmentPageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: equipmentPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:35
* @route '/equipment-page'
*/
equipmentPageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: equipmentPage.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

equipmentPage.form = equipmentPageForm

/**
* @see routes/web.php:39
* @route '/fish-page'
*/
export const fishPage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fishPage.url(options),
    method: 'get',
})

fishPage.definition = {
    methods: ["get","head"],
    url: '/fish-page',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:39
* @route '/fish-page'
*/
fishPage.url = (options?: RouteQueryOptions) => {
    return fishPage.definition.url + queryParams(options)
}

/**
* @see routes/web.php:39
* @route '/fish-page'
*/
fishPage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fishPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:39
* @route '/fish-page'
*/
fishPage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: fishPage.url(options),
    method: 'head',
})

/**
* @see routes/web.php:39
* @route '/fish-page'
*/
const fishPageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fishPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:39
* @route '/fish-page'
*/
fishPageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fishPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:39
* @route '/fish-page'
*/
fishPageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fishPage.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

fishPage.form = fishPageForm

/**
* @see routes/web.php:43
* @route '/flies-page'
*/
export const fliesPage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fliesPage.url(options),
    method: 'get',
})

fliesPage.definition = {
    methods: ["get","head"],
    url: '/flies-page',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:43
* @route '/flies-page'
*/
fliesPage.url = (options?: RouteQueryOptions) => {
    return fliesPage.definition.url + queryParams(options)
}

/**
* @see routes/web.php:43
* @route '/flies-page'
*/
fliesPage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fliesPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:43
* @route '/flies-page'
*/
fliesPage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: fliesPage.url(options),
    method: 'head',
})

/**
* @see routes/web.php:43
* @route '/flies-page'
*/
const fliesPageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fliesPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:43
* @route '/flies-page'
*/
fliesPageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fliesPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:43
* @route '/flies-page'
*/
fliesPageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: fliesPage.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

fliesPage.form = fliesPageForm

/**
* @see routes/web.php:47
* @route '/friends-page'
*/
export const friendsPage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: friendsPage.url(options),
    method: 'get',
})

friendsPage.definition = {
    methods: ["get","head"],
    url: '/friends-page',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:47
* @route '/friends-page'
*/
friendsPage.url = (options?: RouteQueryOptions) => {
    return friendsPage.definition.url + queryParams(options)
}

/**
* @see routes/web.php:47
* @route '/friends-page'
*/
friendsPage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: friendsPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:47
* @route '/friends-page'
*/
friendsPage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: friendsPage.url(options),
    method: 'head',
})

/**
* @see routes/web.php:47
* @route '/friends-page'
*/
const friendsPageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: friendsPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:47
* @route '/friends-page'
*/
friendsPageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: friendsPage.url(options),
    method: 'get',
})

/**
* @see routes/web.php:47
* @route '/friends-page'
*/
friendsPageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: friendsPage.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

friendsPage.form = friendsPageForm
