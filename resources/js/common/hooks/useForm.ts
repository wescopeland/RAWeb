import { useForm as useInertiaForm } from '@inertiajs/react';
import type { useForm as useReactHookForm } from 'react-hook-form';

export function useForm<T>(params: Parameters<typeof useReactHookForm>) {}
