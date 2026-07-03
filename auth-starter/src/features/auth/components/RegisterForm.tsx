'use client';

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useRouter } from 'next/navigation';
import { toast } from 'sonner';
import { useRegister } from '../hooks/useRegister';
import { registerSchema, type RegisterFormData } from '../schemas/authSchemas';
import Button from '@components/atoms/Button';
import Input from '@components/atoms/Input';
import PasswordInput from '@components/molecules/PasswordInput';
import Label from '@components/atoms/Label';
import { ROUTE_PATHS } from '@constants/routes';
import { AUTH_MESSAGES } from '@constants/messages';

export function RegisterForm() {
  const router = useRouter();
  const {register, handleSubmit, formState: {errors}} = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
  });

  const mutation = useRegister({
    onSuccess: () => {toast.success(AUTH_MESSAGES.REGISTER_SUCCESS); router.push(ROUTE_PATHS.DASHBOARD);},
    onError: (e) => toast.error(e),
  });

  return (
    <form onSubmit={handleSubmit((data) => mutation.mutate(data))} className="space-y-4">
      <div>
        <Label required>Email</Label>
        <Input {...register('email')} placeholder="email@example.com" />
        {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
      </div>
      <PasswordInput {...register('password')} label="Password" required error={errors.password?.message} />
      <PasswordInput {...register('confirmPassword')} label="Confirm Password" required error={errors.confirmPassword?.message} />
      <Button type="submit" className="w-full" disabled={mutation.isPending}>
        {mutation.isPending ? 'Creating...' : 'Register'}
      </Button>
      <p className="text-center text-sm">Already have an account? <a href={ROUTE_PATHS.LOGIN} className="text-primary">Login</a></p>
    </form>
  );
}