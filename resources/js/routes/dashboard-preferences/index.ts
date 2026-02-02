import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardPreferencesController::index
* @see app/Http/Controllers/DashboardPreferencesController.php:15
* @route '/api/dashboard-preferences'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/dashboard-preferences',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardPreferencesController::index
* @see app/Http/Controllers/DashboardPreferencesController.php:15
* @route '/api/dashboard-preferences'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardPreferencesController::index
* @see app/Http/Controllers/DashboardPreferencesController.php:15
* @route '/api/dashboard-preferences'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::index
* @see app/Http/Controllers/DashboardPreferencesController.php:15
* @route '/api/dashboard-preferences'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::index
* @see app/Http/Controllers/DashboardPreferencesController.php:15
* @route '/api/dashboard-preferences'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::index
* @see app/Http/Controllers/DashboardPreferencesController.php:15
* @route '/api/dashboard-preferences'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::index
* @see app/Http/Controllers/DashboardPreferencesController.php:15
* @route '/api/dashboard-preferences'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\DashboardPreferencesController::update
* @see app/Http/Controllers/DashboardPreferencesController.php:63
* @route '/api/dashboard-preferences'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/dashboard-preferences',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\DashboardPreferencesController::update
* @see app/Http/Controllers/DashboardPreferencesController.php:63
* @route '/api/dashboard-preferences'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardPreferencesController::update
* @see app/Http/Controllers/DashboardPreferencesController.php:63
* @route '/api/dashboard-preferences'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::update
* @see app/Http/Controllers/DashboardPreferencesController.php:63
* @route '/api/dashboard-preferences'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::update
* @see app/Http/Controllers/DashboardPreferencesController.php:63
* @route '/api/dashboard-preferences'
*/
updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\DashboardPreferencesController::reset
* @see app/Http/Controllers/DashboardPreferencesController.php:111
* @route '/api/dashboard-preferences/reset'
*/
export const reset = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

reset.definition = {
    methods: ["post"],
    url: '/api/dashboard-preferences/reset',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardPreferencesController::reset
* @see app/Http/Controllers/DashboardPreferencesController.php:111
* @route '/api/dashboard-preferences/reset'
*/
reset.url = (options?: RouteQueryOptions) => {
    return reset.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardPreferencesController::reset
* @see app/Http/Controllers/DashboardPreferencesController.php:111
* @route '/api/dashboard-preferences/reset'
*/
reset.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::reset
* @see app/Http/Controllers/DashboardPreferencesController.php:111
* @route '/api/dashboard-preferences/reset'
*/
const resetForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reset.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DashboardPreferencesController::reset
* @see app/Http/Controllers/DashboardPreferencesController.php:111
* @route '/api/dashboard-preferences/reset'
*/
resetForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reset.url(options),
    method: 'post',
})

reset.form = resetForm

const dashboardPreferences = {
    index: Object.assign(index, index),
    update: Object.assign(update, update),
    reset: Object.assign(reset, reset),
}

export default dashboardPreferences