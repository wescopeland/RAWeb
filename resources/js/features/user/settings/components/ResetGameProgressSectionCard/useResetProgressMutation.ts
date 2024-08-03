import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import urlcat from 'urlcat';

import type { FormValues as ResetGameProgressFormValues } from './useResetGameProgressForm';

export function useResetProgressMutation() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (payload: Partial<ResetGameProgressFormValues>) =>
      axios.delete(urlcat('/settings/reset-progress', payload)),

    onSuccess: (_, variables) => {
      if (variables.achievementId === 'all') {
        queryClient.setQueryData<App.Data.UserResettableGame[]>(
          ['resettable-games'],
          (oldData) => oldData?.filter((game) => game.id !== Number(variables.gameId)) ?? [],
        );
      } else {
        queryClient.setQueryData<App.Data.UserResettableGameAchievement[]>(
          ['resettable-game-achievements', variables.gameId],
          (oldData) =>
            oldData?.filter((achievement) => achievement.id !== Number(variables.achievementId)) ??
            [],
        );
      }
    },
  });
}
