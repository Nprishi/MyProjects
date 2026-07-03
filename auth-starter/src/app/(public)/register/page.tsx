import { RegisterForm } from '@features/auth/components/RegisterForm';
import { AuthCard } from '@features/auth/components/AuthCard';

export const metadata = {title: 'Register', description: 'Create your account'};

export default function RegisterPage() {
  return <AuthCard title="Register" description="Create new account"><RegisterForm /></AuthCard>;
}