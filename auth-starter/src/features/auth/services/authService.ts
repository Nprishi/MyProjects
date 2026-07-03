import { authApi } from '../api/authApi';
import { authStore } from '../stores/authStore';

export const authService = {
  async login(email: string, password: string) {
    const response = await authApi.login(email, password);
    authStore.getState().setToken(response.access_token);
    authStore.getState().setUser(response.user);
  },

  async register(email: string, password: string) {
    const response = await authApi.register(email, password);
    authStore.getState().setToken(response.access_token);
    authStore.getState().setUser(response.user);
  },

  logout() {
    authStore.getState().logout();
  },

  hydrate() {
    authStore.getState().hydrate();
  },
};