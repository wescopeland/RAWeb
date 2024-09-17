import { useQueryClient } from '@tanstack/react-query';
import type { Row } from '@tanstack/react-table';
import { RxDotsHorizontal } from 'react-icons/rx';

import { BaseButton } from '@/common/components/+vendor/BaseButton';
import {
  BaseDropdownMenu,
  BaseDropdownMenuContent,
  BaseDropdownMenuItem,
  BaseDropdownMenuTrigger,
} from '@/common/components/+vendor/BaseDropdownMenu';
import { toastMessage } from '@/common/components/+vendor/BaseToaster';
import { useRemoveFromBacklogMutation } from '@/common/hooks/useRemoveFromBacklogMutation';

interface DataTableRowActionsProps<TData> {
  row: Row<TData>;
}

export function DataTableRowActions<TData>({ row }: DataTableRowActionsProps<TData>) {
  const queryClient = useQueryClient();

  const removeFromBacklogMutation = useRemoveFromBacklogMutation();

  const handleRemoveFromBacklogClick = () => {
    const rowData = row.original as { game?: App.Platform.Data.Game };

    const gameId = rowData?.game?.id;
    if (gameId) {
      toastMessage.promise(removeFromBacklogMutation.mutateAsync(gameId), {
        loading: 'Removing...',
        success: () => {
          queryClient.invalidateQueries({ queryKey: ['data'] });

          return 'Removed!';
        },
        error: 'Something went wrong.',
      });
    }
  };

  return (
    <BaseDropdownMenu>
      <BaseDropdownMenuTrigger asChild>
        <BaseButton
          variant="ghost"
          className="flex h-8 w-8 p-0 text-link data-[state=open]:bg-neutral-950/80 light:data-[state=open]:bg-neutral-400"
        >
          <RxDotsHorizontal className="h-4 w-4" />
          <span className="sr-only">Open menu</span>
        </BaseButton>
      </BaseDropdownMenuTrigger>

      <BaseDropdownMenuContent align="end" className="w-[160px]">
        <BaseDropdownMenuItem onClick={handleRemoveFromBacklogClick}>Remove</BaseDropdownMenuItem>
      </BaseDropdownMenuContent>
    </BaseDropdownMenu>
  );
}
