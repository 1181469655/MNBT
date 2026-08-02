import { Request, Response, NextFunction } from 'express';
import { getOne } from '../db';

declare module 'express-session' {
  interface SessionData {
    userId?: number;
    username?: string;
    role?: string;
    captchaText?: string;
  }
}

export function requireLogin(req: Request, res: Response, next: NextFunction): void {
  if (!req.session || !req.session.userId) {
    res.status(401).json({ code: 401, msg: '请先登录' });
    return;
  }
  // Refresh user info
  const user = getOne("SELECT id, username, role, status FROM users WHERE id = ?", [req.session.userId]);
  if (!user || user.status === 'banned') {
    req.session.destroy(() => {});
    res.status(401).json({ code: 401, msg: '账号不可用' });
    return;
  }
  next();
}

export function requireDeveloper(req: Request, res: Response, next: NextFunction): void {
  requireLogin(req, res, () => {
    if (req.session.role !== 'developer' && req.session.role !== 'admin') {
      res.status(403).json({ code: 403, msg: '需要开发者权限' });
      return;
    }
    next();
  });
}

export function requireAdmin(req: Request, res: Response, next: NextFunction): void {
  requireLogin(req, res, () => {
    if (req.session.role !== 'admin') {
      res.status(403).json({ code: 403, msg: '需要管理员权限' });
      return;
    }
    next();
  });
}
