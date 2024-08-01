import type { FC, ReactNode } from 'react';
import type { FieldValues, UseFormReturn } from 'react-hook-form';

import { BaseButton } from '@/common/components/+vendor/BaseButton';
import {
  BaseCard,
  BaseCardContent,
  BaseCardFooter,
  BaseCardHeader,
  BaseCardTitle,
} from '@/common/components/+vendor/BaseCard';
import { BaseForm } from '@/common/components/+vendor/BaseForm';

interface SettingsFormCardProps {
  title: string;
  children: ReactNode;
  formMethods: UseFormReturn<any>;
  onSubmit: (formValues: any) => void;
  isSubmitting: boolean;
}

export const SettingsFormCard: FC<SettingsFormCardProps> = ({
  title,
  children,
  formMethods,
  onSubmit,
  isSubmitting,
}) => {
  return (
    <BaseCard className="w-full">
      <BaseForm {...formMethods}>
        <form onSubmit={formMethods.handleSubmit(onSubmit)}>
          <BaseCardHeader className="pb-2">
            <BaseCardTitle>{title}</BaseCardTitle>
          </BaseCardHeader>

          <BaseCardContent>{children}</BaseCardContent>

          <BaseCardFooter>
            <div className="w-full flex justify-end">
              <BaseButton type="submit" size="sm" disabled={isSubmitting}>
                Update
              </BaseButton>
            </div>
          </BaseCardFooter>
        </form>
      </BaseForm>
    </BaseCard>
  );
};
