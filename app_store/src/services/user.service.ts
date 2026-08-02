import bcrypt from 'bcryptjs';
import { run, getOne, getAll } from '../db';

export interface User {
  id: number;
  username: string;
  email: string;
  role: string;
  avatar: string;
  bio: string;
  status: string;
  created_at: string;
  updated_at: string;
}

export function register(username: string, password: string, email: string): { ok: boolean; msg: string; user?: User } {
  const existing = getOne("SELECT id FROM users WHERE username = ? OR email = ?", [username, email]);
  if (existing) {
    return { ok: false, msg: '用户名或邮箱已被注册' };
  }

  const hash = bcrypt.hashSync(password, 10);
  const now = new Date().toISOString();
  run(
    "INSERT INTO users (username, password, email, role, status, created_at, updated_at) VALUES (?, ?, ?, 'developer', 'active', ?, ?)",
    [username, hash, email, now, now]
  );

  const user = getOne("SELECT id, username, email, role, avatar, bio, status, created_at, updated_at FROM users WHERE username = ?", [username]);
  return { ok: true, msg: '注册成功', user };
}

export function login(username: string, password: string): { ok: boolean; msg: string; user?: User } {
  const user = getOne("SELECT * FROM users WHERE username = ?", [username]);
  if (!user) {
    return { ok: false, msg: '用户名或密码错误' };
  }
  if (user.status === 'banned') {
    return { ok: false, msg: '账号已被封禁' };
  }
  if (!bcrypt.compareSync(password, user.password)) {
    return { ok: false, msg: '用户名或密码错误' };
  }
  const { password: _, ...safeUser } = user;
  return { ok: true, msg: '登录成功', user: safeUser };
}

export function getUserById(id: number): User | undefined {
  const user = getOne("SELECT id, username, email, role, avatar, bio, status, created_at, updated_at FROM users WHERE id = ?", [id]);
  if (!user) return undefined;
  return user;
}

export function changePassword(userId: number, oldPassword: string, newPassword: string): { ok: boolean; msg: string } {
  const user = getOne("SELECT * FROM users WHERE id = ?", [userId]);
  if (!user) {
    return { ok: false, msg: '用户不存在' };
  }
  if (!bcrypt.compareSync(oldPassword, user.password)) {
    return { ok: false, msg: '旧密码错误' };
  }
  const hash = bcrypt.hashSync(newPassword, 10);
  const now = new Date().toISOString();
  run("UPDATE users SET password = ?, updated_at = ? WHERE id = ?", [hash, now, userId]);
  return { ok: true, msg: '密码修改成功' };
}

export function listUsers(page: number = 1, pageSize: number = 20): { users: User[]; total: number } {
  const offset = (page - 1) * pageSize;
  const users = getAll(
    "SELECT id, username, email, role, avatar, bio, status, created_at, updated_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?",
    [pageSize, offset]
  );
  const countRow = getOne("SELECT COUNT(*) as cnt FROM users");
  return { users, total: countRow?.cnt || 0 };
}

export function updateUserStatus(userId: number, status: string): { ok: boolean; msg: string } {
  const user = getOne("SELECT id FROM users WHERE id = ?", [userId]);
  if (!user) {
    return { ok: false, msg: '用户不存在' };
  }
  const now = new Date().toISOString();
  run("UPDATE users SET status = ?, updated_at = ? WHERE id = ?", [status, now, userId]);
  return { ok: true, msg: '更新成功' };
}
