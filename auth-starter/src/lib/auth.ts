import { getLocalStorage, setLocalStorage, removeLocalStorage } from '@utils/localStorage';

const TOKEN_KEY = 'auth_token';
const USER_KEY = 'auth_user';

export const authTokenManager = {
  getToken: () => getLocalStorage<string>(TOKEN_KEY),
  setToken: (token: string) => setLocalStorage(TOKEN_KEY, token),
  removeToken: () => removeLocalStorage(TOKEN_KEY),
  getUser: () => getLocalStorage(USER_KEY),
  setUser: (user: any) => setLocalStorage(USER_KEY, user),
  removeUser: () => removeLocalStorage(USER_KEY),
  clear: () => {authTokenManager.removeToken(); authTokenManager.removeUser();},
};