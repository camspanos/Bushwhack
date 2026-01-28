import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\UserLocationsController::index
* @see app/Http/Controllers/UserLocationsController.php:20
* @route '/countries'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/countries',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UserLocationsController::index
* @see app/Http/Controllers/UserLocationsController.php:20
* @route '/countries'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserLocationsController::index
* @see app/Http/Controllers/UserLocationsController.php:20
* @route '/countries'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserLocationsController::index
* @see app/Http/Controllers/UserLocationsController.php:20
* @route '/countries'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\UserLocationsController::index
* @see app/Http/Controllers/UserLocationsController.php:20
* @route '/countries'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserLocationsController::index
* @see app/Http/Controllers/UserLocationsController.php:20
* @route '/countries'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserLocationsController::index
* @see app/Http/Controllers/UserLocationsController.php:20
* @route '/countries'
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

const countries = {
    index: Object.assign(index, index),
}

export default countries