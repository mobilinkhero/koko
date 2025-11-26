// resources/js/theme/createSwatches.js
import { DEFAULT_PALETTE_CONFIG } from './constants'
import {
  hexToHSL,
  HSLToHex,
  lightnessFromHSLum,
  luminanceFromHex,
} from './helpers'
import {
  createDistributionValues,
  createHueScale,
  createSaturationScale,
} from './scales'

export function createSwatches(palette) {
  const { value, valueStop } = palette
  const useLightness = palette.useLightness ?? DEFAULT_PALETTE_CONFIG.useLightness
  const h = palette.h ?? DEFAULT_PALETTE_CONFIG.h
  const s = palette.s ?? DEFAULT_PALETTE_CONFIG.s
  const lMin = palette.lMin ?? DEFAULT_PALETTE_CONFIG.lMin
  const lMax = palette.lMax ?? DEFAULT_PALETTE_CONFIG.lMax

  const hueScale = createHueScale(h, valueStop)
  const saturationScale = createSaturationScale(s, valueStop)
  const { h: valueH, s: valueS, l: valueL } = hexToHSL(value)
  const lightnessValue = useLightness ? valueL : luminanceFromHex(value)
  const distributionScale = createDistributionValues(lMin, lMax, lightnessValue, valueStop)

  const swatches = hueScale.map(({ stop }, stopIndex) => {
    const newH = valueH + hueScale[stopIndex].tweak
    const newS = valueS + saturationScale[stopIndex].tweak
    const newL = useLightness
      ? distributionScale[stopIndex].tweak
      : lightnessFromHSLum(newH, newS, distributionScale[stopIndex].tweak)

    const newHex = HSLToHex(newH, newS, newL)

    return {
      stop,
      hex: stop === valueStop ? `#${value.toUpperCase()}` : newHex.toUpperCase(),
      h: newH,
      hScale: hueScale[stopIndex].tweak,
      s: newS,
      sScale: saturationScale[stopIndex].tweak,
      l: newL,
    }
  })

  return swatches
}
