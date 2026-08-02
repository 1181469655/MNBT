import { Router, Request, Response } from 'express';
import svgCaptcha from 'svg-captcha';
import { register, login, changePassword, getUserById } from '../services/user.service';
import { requireLogin } from '../middleware/auth';

const router = Router();

function verifyCaptcha(req: Request, res: Response): boolean {
  const { captcha } = req.body;
  const sessionCaptcha = req.session.captchaText;
  if (!captcha || !sessionCaptcha) {
    res.json({ code: 400, msg: '请输入验证码' });
    return false;
  }
  if (captcha.toLowerCase() !== (sessionCaptcha as string).toLowerCase()) {
    res.json({ code: 400, msg: '验证码错误' });
    return false;
  }
  delete req.session.captchaText;
  return true;
}

router.get('/captcha', (req: Request, res: Response) => {
  const captcha = svgCaptcha.create({
    size: 4,
    noise: 3,
    color: true,
    background: '#f0f2f5',
    width: 120,
    height: 42,
    fontSize: 48,
    ignoreChars: '0oOiIlL1'
  });
  req.session.captchaText = captcha.text;
  res.type('svg');
  res.send(captcha.data);
});

router.post('/register', (req: Request, res: Response) => {
  if (!verifyCaptcha(req, res)) return;

  const { username, password, email } = req.body;
  if (!username || !password || !email) {
    res.json({ code: 400, msg: '请填写所有必填字段' });
    return;
  }
  if (username.length < 2 || username.length > 30) {
    res.json({ code: 400, msg: '用户名长度 2-30 字符' });
    return;
  }
  if (password.length < 6) {
    res.json({ code: 400, msg: '密码至少 6 位' });
    return;
  }
  const result = register(username, password, email);
  if (result.ok && result.user) {
    req.session.userId = result.user.id;
    req.session.username = result.user.username;
    req.session.role = result.user.role;
  }
  res.json({ code: result.ok ? 0 : 400, msg: result.msg, data: result.user });
});

router.post('/login', (req: Request, res: Response) => {
  if (!verifyCaptcha(req, res)) return;

  const { username, password } = req.body;
  if (!username || !password) {
    res.json({ code: 400, msg: '请填写用户名和密码' });
    return;
  }
  const result = login(username, password);
  if (result.ok && result.user) {
    req.session.userId = result.user.id;
    req.session.username = result.user.username;
    req.session.role = result.user.role;
  }
  res.json({ code: result.ok ? 0 : 400, msg: result.msg, data: result.user });
});

router.post('/logout', (req: Request, res: Response) => {
  req.session.destroy(() => {
    res.json({ code: 0, msg: '已退出登录' });
  });
});

router.get('/me', requireLogin, (req: Request, res: Response) => {
  const user = getUserById(req.session.userId!);
  if (!user) {
    res.json({ code: 401, msg: '用户不存在' });
    return;
  }
  res.json({ code: 0, data: user });
});

router.put('/password', requireLogin, (req: Request, res: Response) => {
  const { oldPassword, newPassword } = req.body;
  if (!oldPassword || !newPassword) {
    res.json({ code: 400, msg: '请填写旧密码和新密码' });
    return;
  }
  if (newPassword.length < 6) {
    res.json({ code: 400, msg: '新密码至少 6 位' });
    return;
  }
  const result = changePassword(req.session.userId!, oldPassword, newPassword);
  res.json({ code: result.ok ? 0 : 400, msg: result.msg });
});

export default router;
