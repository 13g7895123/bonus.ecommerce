const buildAuthHeaders = (headers = {}) => {
  const token = localStorage.getItem('token')
  if (!token) return headers

  return {
    ...headers,
    Authorization: `Bearer ${token}`,
  }
}

const extractMessage = async (response) => {
  try {
    const data = await response.clone().json()
    return data?.message || ''
  } catch {
    return ''
  }
}

export const apiFetch = async (url, options = {}) => {
  const { auth = false, headers = {}, ...rest } = options
  const response = await fetch(url, {
    ...rest,
    headers: auth ? buildAuthHeaders(headers) : headers,
  })

  if (response.status === 401) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    window.dispatchEvent(new CustomEvent('auth:expired'))
  }

  if (response.status === 403) {
    const message = await extractMessage(response)
    if (message.includes('尚未綁定設備')) {
      window.dispatchEvent(new CustomEvent('device:unbound', { detail: { message } }))
    }
  }

  return response
}

