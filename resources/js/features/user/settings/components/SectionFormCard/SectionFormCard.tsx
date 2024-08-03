import type { FC, ReactNode } from 'react';
import type { UseFormReturn } from 'react-hook-form';

import type { BaseButtonProps } from '@/common/components/+vendor/BaseButton';
import { BaseButton } from '@/common/components/+vendor/BaseButton';
import {
  BaseCard,
  BaseCardContent,
  BaseCardFooter,
  BaseCardHeader,
  BaseCardTitle,
} from '@/common/components/+vendor/BaseCard';
import { BaseForm } from '@/common/components/+vendor/BaseForm';

interface SectionFormCardProps {
  title: string;
  children: ReactNode;
  formMethods: UseFormReturn<any>;
  onSubmit: (formValues: any) => void;
  isSubmitting: boolean;

  buttonProps?: BaseButtonProps;
}

export const SectionFormCard: FC<SectionFormCardProps> = ({
  title,
  children,
  formMethods,
  onSubmit,
  isSubmitting,
  buttonProps,
}) => {
  return (
    <BaseCard className="w-full">
      <BaseCardHeader className="pb-4">
        <BaseCardTitle>{title}</BaseCardTitle>
      </BaseCardHeader>

      <BaseForm {...formMethods}>
        <form onSubmit={formMethods.handleSubmit(onSubmit)}>
          <BaseCardContent>{children}</BaseCardContent>

          <BaseCardFooter>
            <div className="flex w-full justify-end">
              <BaseButton type="submit" disabled={isSubmitting} {...buttonProps}>
                {buttonProps?.children ?? 'Update'}
              </BaseButton>
            </div>
          </BaseCardFooter>
        </form>
      </BaseForm>
    </BaseCard>
  );
};
