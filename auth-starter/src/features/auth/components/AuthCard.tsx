import Card from '@components/atoms/Card';

interface AuthCardProps {
  title: string;
  description?: string;
  children: React.ReactNode;
}

export function AuthCard({title, description, children}: AuthCardProps) {
  return (
    <Card className="w-full max-w-md">
      <h1 className="text-2xl font-bold">{title}</h1>
      {description && <p className="text-sm text-muted-foreground">{description}</p>}
      <div className="mt-4">{children}</div>
    </Card>
  );
}