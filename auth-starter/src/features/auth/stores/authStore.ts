import { create } from 'zustand';
import { authTokenManager } from '@lib/auth';
import type { AuthState, AuthUser } from '../types/auth';

interface AuthStore extends AuthState {
  setToken: (token: string) => void;
  setUser: (user: AuthUser) => void;
  logout: () => void;
  hydrate: () => void;
}

export const authStore = create<AuthStore>((set) => ({
  token: null,
  user: null,
  isAuthenticated: false,

  setToken: (token) => {
    authTokenManager.setToken(token);
    set({ token, isAuthenticated: !!token });
  },

  setUser: (user) => {
    authTokenManager.setUser(user);
    set({ user });
  },

  logout: () => {
    authTokenManager.clear();
    set({ token: null, user: null, isAuthenticated: false });
  },

  hydrate: () => {
    const token = authTokenManager.getToken();
    const user = authTokenManager.getUser();
    if (token && user) {
      set({ token, user, isAuthenticated: true });
    }
  },
}));