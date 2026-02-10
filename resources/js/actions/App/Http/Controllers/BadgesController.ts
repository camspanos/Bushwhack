import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BadgesController::index
* @see app/Http/Controllers/BadgesController.php:21
* @route '/badges-page'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/badges-page',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BadgesController::index
* @see app/Http/Controllers/BadgesController.php:21
* @route '/badges-page'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BadgesController::index
* @see app/Http/Controllers/BadgesController.php:21
* @route '/badges-page'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::index
* @see app/Http/Controllers/BadgesController.php:21
* @route '/badges-page'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BadgesController::index
* @see app/Http/Controllers/BadgesController.php:21
* @route '/badges-page'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::index
* @see app/Http/Controllers/BadgesController.php:21
* @route '/badges-page'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::index
* @see app/Http/Controllers/BadgesController.php:21
* @route '/badges-page'
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
* @see \App\Http\Controllers\BadgesController::getUnnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
export const getUnnotified = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getUnnotified.url(options),
    method: 'get',
})

getUnnotified.definition = {
    methods: ["get","head"],
    url: '/badges/unnotified',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BadgesController::getUnnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
getUnnotified.url = (options?: RouteQueryOptions) => {
    return getUnnotified.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BadgesController::getUnnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
getUnnotified.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getUnnotified.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::getUnnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
getUnnotified.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getUnnotified.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BadgesController::getUnnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
const getUnnotifiedForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getUnnotified.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::getUnnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
getUnnotifiedForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getUnnotified.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BadgesController::getUnnotified
* @see app/Http/Controllers/BadgesController.php:106
* @route '/badges/unnotified'
*/
getUnnotifiedForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getUnnotified.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

getUnnotified.form = getUnnotifiedForm

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

const BadgesController = { index, getUnnotified, markNotified }

export default BadgesController