import { HttpError } from '@types/errors';
import { AUTH_MESSAGES } from '@constants/messages';

export function getErrorMessage(error: unknown): string {
  if (error instanceof HttpError) {
    return error.message;
  }
  if (error instanceof Error) {
    return error.message;
  }
  return AUTH_MESSAGES.UNKNOWN_ERROR;
}