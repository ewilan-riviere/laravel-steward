import type { Alpine, AlpineComponent } from 'alpinejs'

interface Cookie {
  id: number
  name: string
  value: string
}

export interface CookieConsentType {
  show: (show: boolean) => void
  hide: () => void
  run: (options: any) => void
}

/**
 * AlpineJS plugin for GDPR
 *
 * docs: https://cookieconsent.orestbida.com
 */
export default (Alpine: Alpine) => {
  Alpine.data('gdpr', (): AlpineComponent<{
    cookies: Cookie[]
    cookieConsent: CookieConsentType
    getCookies(): Cookie[]
    fetchCookieConsent(): Promise<CookieConsentType>
    showGdpr(): void
    hideGdpr(): void
    getCookie(name: string): string | undefined
    setCookie(name: string, value: any, days?: number): void
    delCookie(name: string): void
    delCookieAll(): void
  }> => ({
    cookies: [] as Cookie[],
    cookieConsent: {} as CookieConsentType,

    async init() {
      this.cookies = this.getCookies()
      await this.fetchCookieConsent()
    },
    async fetchCookieConsent() {
      try {
        const cookie = await import('vanilla-cookieconsent')
        return cookie as CookieConsentType
      }
      catch (error) {
        return {
          show: () => {},
          hide: () => {},
          run: () => {},
        }
      }
    },
    showGdpr() {
      this.cookieConsent.show(true)
    },
    hideGdpr() {
      this.cookieConsent.hide()
    },
    getCookies(): Cookie[] {
      const items = document.cookie.split(';')
      const list: Cookie[] = []

      for (let i = 1; i <= items.length; i++) {
        let item = `${items[i - 1]}`
        item = item.trim()
        const name = item.split('=')[0]
        const value = item.split('=')[1]

        list.push({
          id: i,
          name,
          value,
        })
      }

      return list
    },
    getCookie(name: string): string | undefined {
      const nameEQ = `${name}=`
      const ca = document.cookie.split(';')
      for (let i = 0; i < ca.length; i++) {
        let c = ca[i]
        while (c.charAt(0) === ' ') c = c.substring(1, c.length)
        if (c.indexOf(nameEQ) === 0) {
          const content = c.substring(nameEQ.length, c.length)
          return JSON.parse(decodeURIComponent(content))
        }
      }

      return undefined
    },
    setCookie(name: string, value: any, days = 365) {
      if (typeof value === 'object')
        value = JSON.stringify(value)
      let expires = ''
      if (days) {
        const date = new Date()
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000))
        expires = `; expires=${date.toUTCString()}`
      }
      document.cookie = `${name}=${value || ''}${expires}; path=/; SameSite=Lax;`
    },
    delCookie(name: string) {
      document.cookie = `${name}=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;`
    },
    delCookieAll() {
      const cookies = document.cookie.split(';')

      for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i]
        const eqPos = cookie.indexOf('=')
        const name = eqPos > -1 ? cookie.substring(0, eqPos) : cookie
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax;`
      }
    },
  }))
}
