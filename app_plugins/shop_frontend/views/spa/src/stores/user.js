import { defineStore } from 'pinia';
import api from '../api';

export const useUserStore = defineStore('user', {
  state: () => ({ user: null, loaded: false }),
  getters: {
    isLogin: (s) => !!s.user,
  },
  actions: {
    async fetch() {
      try { const r = await api.get('user'); this.user = r.data.user; } catch (e) { this.user = null; }
      this.loaded = true;
    },
    async login(u, p) {
      const r = await api.post('login', { username: u, password: p });
      if (r.data.redirect) { await this.fetch(); return true; }
      throw new Error(r.data.code || '登录失败');
    },
    async register(d) {
      const r = await api.post('register', d);
      if (r.data.redirect) { await this.fetch(); return true; }
      throw new Error(r.data.code || '注册失败');
    },
    async logout() { await api.post('logout'); this.user = null; },
  },
});
