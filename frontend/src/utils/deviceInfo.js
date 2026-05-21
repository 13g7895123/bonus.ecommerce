export const collectDeviceInfo = async () => {
  const nav = window.navigator || {}
  const info = {
    userAgent: nav.userAgent || '',
    platform: nav.platform || '',
  }

  if (nav.userAgentData) {
    info.userAgentData = {
      mobile: nav.userAgentData.mobile,
      platform: nav.userAgentData.platform,
      brands: nav.userAgentData.brands || [],
    }

    try {
      const highEntropy = await nav.userAgentData.getHighEntropyValues([
        'model',
      ])
      info.userAgentData = { ...info.userAgentData, ...highEntropy }
    } catch {
      // Some browsers deliberately limit device model details.
    }
  }

  return info
}
