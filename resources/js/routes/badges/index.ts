import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\BadgesController::unnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
export const unnotified = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unnotified.url(options),
    method: 'get',
})

unnotified.definition = {
    methods: ["get","head"],
    url: '/badges/unnotified',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BadgesController::unnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
unnotified.url = (options?: RouteQueryOptions) => {
    return unnotified.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BadgesController::unnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
unnotified.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unnotified.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::unnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
unnotified.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: unnotified.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BadgesController::unnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
const unnotifiedForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: unnotified.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::unnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
unnotifiedForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: unnotified.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::unnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
unnotifiedForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: unnotified.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

unnotified.form = unnotifiedForm

/**
* @see \App\Http\Controllers\BadgesController::markNotified
* @see app/Http/Controllers/BadgesController.php:130
* @route '/badges/mark-notified'
*/
export const markNotified = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markNotified.url(options),
    method: 'post',
})

markNotified.definition = {
    methods: ["post"],
    url: '/badges/mark-notified',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BadgesController::markNotified
* @see app/Http/Controllers/BadgesController.php:130
* @route '/badges/mark-notified'
*/
markNotified.url = (options?: RouteQueryOptions) => {
    return markNotified.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BadgesController::markNotified
* @see app/Http/Controllers/BadgesController.php:130
* @route '/badges/mark-notified'
*/
markNotified.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markNotified.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BadgesController::markNotified
* @see app/Http/Controllers/BadgesController.php:130
* @route '/badges/mark-notified'
*/
const markNotifiedForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markNotified.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BadgesController::markNotified
* @see app/Http/Controllers/BadgesController.php:130
* @route '/badges/mark-notified'
*/
markNotifiedForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markNotified.url(options),
    method: 'post',
})

markNotified.form = markNotifiedForm

const badges = {
    unnotified: Object.assign(unnotified, unnotified),
    markNotified: Object.assign(markNotified, markNotified),
}

export default badges