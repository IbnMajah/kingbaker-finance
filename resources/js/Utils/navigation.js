/**
 * Path without leading slash or query string.
 */
export function currentPath(pageUrl) {
  return (pageUrl || '').split('?')[0].replace(/^\//, '')
}

/**
 * True when the current Inertia URL is exactly `prefix` or a nested path of it.
 * Uses path segments so `cashiers` does not match `cashier-variances`.
 */
export function isCurrentUrl(pageUrl, ...prefixes) {
  const path = currentPath(pageUrl)

  return prefixes.some((prefix) => {
    if (prefix === '') {
      return path === ''
    }

    return path === prefix || path.startsWith(`${prefix}/`)
  })
}

export function resolveMainMenu(pageUrl) {
  const path = currentPath(pageUrl)

  if (path === '') {
    return 'overview'
  }

  if (matches(path, ['bank-accounts', 'transactions', 'deposits', 'cashier-variances'])) {
    return 'accounts'
  }

  if (matches(path, ['sales', 'invoices', 'contacts'])) {
    return 'sales'
  }

  if (matches(path, ['bills', 'vendors', 'cheque-payments', 'payment-categories'])) {
    return 'vendors'
  }

  if (matches(path, ['expense-claims', 'miscellaneous'])) {
    return 'expenses'
  }

  if (matches(path, ['reports'])) {
    return 'reports'
  }

  if (matches(path, ['branches', 'users', 'roles', 'cashier-assignments', 'cashiers', 'shifts'])) {
    return 'organization'
  }

  return null
}

function matches(path, prefixes) {
  return prefixes.some((prefix) => path === prefix || path.startsWith(`${prefix}/`))
}
