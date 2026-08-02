declare module 'svg-captcha' {
  interface CaptchaOptions {
    size?: number;
    noise?: number;
    color?: boolean;
    background?: string;
    width?: number;
    height?: number;
    fontSize?: number;
    ignoreChars?: string;
  }
  interface CaptchaResult {
    data: string;
    text: string;
  }
  export function create(options?: CaptchaOptions): CaptchaResult;
  export function createMath(options?: CaptchaOptions): CaptchaResult;
}
