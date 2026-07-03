import axios from 'axios';
import { API_BASE_URL, API_TIMEOUT } from '@constants/api';
import { authStore } from '@stores/index';

const axiosInstance = axios.create({
  baseURL: API_BASE_URL,
  timeout: API_TIMEOUT,
});

axiosInstance.interceptors.request.use((config) => {
  const token = authStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default axiosInstance;