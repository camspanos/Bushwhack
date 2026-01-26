import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::edit
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:16
* @route '/settings/following'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/following',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::edit
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:16
* @route '/settings/following'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::edit
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:16
* @route '/settings/following'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::edit
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:16
* @route '/settings/following'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::edit
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:16
* @route '/settings/following'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::edit
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:16
* @route '/settings/following'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::edit
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:16
* @route '/settings/following'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::update
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:26
* @route '/settings/following'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/settings/following',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::update
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:26
* @route '/settings/following'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::update
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:26
* @route '/settings/following'
*/
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::update
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:26
* @route '/settings/following'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\FollowingSettingsController::update
* @see app/Http/Controllers/Settings/FollowingSettingsController.php:26
* @route '/settings/following'
*/
updateForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const FollowingSettingsController = { edit, update }

export default FollowingSettingsController