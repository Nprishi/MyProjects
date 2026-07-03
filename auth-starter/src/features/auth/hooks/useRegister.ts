import { useMutation } from '@tanstack/react-query';
import { authService } from '../services/authService';
import { getErrorMessage } from '@utils/errors';

export function useRegister(options?: {onSuccess?: () => void; onError?: (error: string) => void}) {
  return useMutation({
    mutationFn: ({email, password}: {email: string; password: string}) => authService.register(email, password),
    onSuccess: options?.onSuccess,
    onError: (error) => options?.onError?.(getErrorMessage(error)),
  });
}