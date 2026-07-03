import { useEffect } from 'react';
import { authStore } from '../stores/authStore';

export function useAuth() {
  useEffect(() => {
    authStore.getState().hydrate();
  }, []);

  return authStore((state) => ({
    token: state.token,
    user: state.user,
    isAuthenticated: state.isAuthenticated,
  }));
}