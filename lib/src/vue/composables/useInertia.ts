import { inject } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { IInertiaTyped, InertiaTypedOptions, RequestPayload, Route } from '../../types/index.js'

export const useInertia = () => {
  const inertia = inject('inertia') as IInertiaTyped
  const options = inertia.options as InertiaTypedOptions
  const inertiaRouter = options.router

  const convertURL = (url: Route) => {
    return inertia.route(url)
  }

  const router = {
    get: (url: Route, data?: RequestPayload) => inertiaRouter?.get(convertURL(url), data),
    post: (url: Route, data?: RequestPayload) => inertiaRouter?.post(convertURL(url), data),
    patch: (url: Route, data?: RequestPayload) => inertiaRouter?.patch(convertURL(url), data),
    put: (url: Route, data?: RequestPayload) => inertiaRouter?.put(convertURL(url), data),
    delete: (url: Route) => inertiaRouter?.delete(convertURL(url)),
  }

  return {
    router,
    route: inertia.route,
    isRoute: inertia.isRoute,
    currentRoute: inertia.currentRoute,
    page: usePage() as InertiaPage,
  }
}
