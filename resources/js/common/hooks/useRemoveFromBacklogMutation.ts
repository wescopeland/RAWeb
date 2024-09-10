import { useMutation } from '@tanstack/react-query';
import axios from 'axios';

export function useRemoveFromBacklogMutation() {
  return useMutation({
    mutationFn: (gameId: number) => axios.delete(route('api.user-game-list.destroy', gameId)),
  });
}
