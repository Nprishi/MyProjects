import axiosInstance from '@lib/axios';
import { API_ENDPOINTS } from '@constants/api';
import type { AuthResponse } from '../types/auth';

export const authApi = {
  login: async (email: string, password: string): Promise<AuthResponse> => {
    const { data } = await axiosInstance.post(API_ENDPOINTS.AUTH.LOGIN, { email, password });
    return data;
  },

  register: async (email: string, password: string): Promise<AuthResponse> => {
    const { data } = await axiosInstance.post(API_ENDPOINTS.AUTH.REGISTER, { email, password });
    return data;
  },
};