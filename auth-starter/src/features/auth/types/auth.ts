export interface AuthUser {
  id: string;
  email: string;
}

export interface AuthResponse {
  access_token: string;
  user: AuthUser;
}

export interface AuthState {
  token: string | null;
  user: AuthUser | null;
  isAuthenticated: boolean;
}