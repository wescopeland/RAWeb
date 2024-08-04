declare namespace App.Data {
  export type ForumTopicComment = {
    id: number;
    body: string;
    createdAt: string;
    updatedAt: string;
    user: App.Data.User;
    authorized: boolean;
    forumTopicId: number | null;
  };
  export type ForumTopic = {
    id: number;
    title: string;
    createdAt: string;
    user: App.Data.User | null;
    latestComment?: App.Data.ForumTopicComment;
    commentCount24h?: number;
    oldestComment24hId?: number;
    commentCount7d?: number;
    oldestComment7dId?: number;
  };
  export type __UNSAFE_PaginatedData = {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
    items: Array<any>;
    links: {
      firstPageUrl: string | null;
      lastPageUrl: string | null;
      previousPageUrl: string | null;
      nextPageUrl: string | null;
    };
  };
  export type User = {
    displayName: string;
    avatarUrl: string;
    id?: number;
    username?: string;
    motto?: string;
    legacyPermissions?: number;
    preferences?: { prefersAbsoluteDates: boolean };
    roles?: App.Models.UserRole[];
    apiKey?: string;
    deleteRequested?: string;
    emailAddress?: string;
    unreadMessageCount?: number;
    userWallActive?: boolean;
    websitePrefs?: number;
    canUpdateAvatar?: boolean;
  };
  export type UserResettableGameAchievement = {
    id: number;
    title: string;
    points: number;
    isHardcore: boolean;
  };
  export type UserResettableGame = {
    id: number;
    title: string;
    consoleName: string;
    numAwarded: number;
    numPossible: number;
  };
}
declare namespace App.Enums {
  export type UserPreference =
    | 0
    | 1
    | 2
    | 3
    | 4
    | 5
    | 6
    | 7
    | 8
    | 9
    | 10
    | 11
    | 12
    | 13
    | 14
    | 15
    | 16
    | 17;
}
declare namespace App.Models {
  export type UserRole =
    | 'root'
    | 'administrator'
    | 'release-manager'
    | 'game-hash-manager'
    | 'developer-staff'
    | 'developer'
    | 'developer-junior'
    | 'artist'
    | 'writer'
    | 'game-editor'
    | 'play-tester'
    | 'moderator'
    | 'forum-manager'
    | 'ticket-manager'
    | 'news-manager'
    | 'event-manager'
    | 'cheat-investigator'
    | 'founder'
    | 'architect'
    | 'engineer'
    | 'team-account'
    | 'beta'
    | 'developer-veteran';
}
