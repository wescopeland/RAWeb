import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import urlcat from 'urlcat';

export function useResettableGameAchievementsQuery(gameId: string | null) {
  return useQuery({
    queryKey: ['resettable-game-achievements', gameId],
    queryFn: async () => {
      const response = await axios.get<{ results: App.Data.UserResettableGameAchievement[] }>(
        urlcat('/settings/resettable-game-achievements', { gameId }),
      );

      return response.data.results;
    },
    enabled: !!gameId,
    refetchInterval: false,
    staleTime: 60 * 1000, // one minute
  });
}
