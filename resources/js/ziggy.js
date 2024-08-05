const Ziggy = {"url":"http:\/\/localhost:64000","port":64000,"defaults":{},"routes":{"forum.post.edit":{"uri":"forums\/post\/{forumTopicComment}\/edit","methods":["GET"],"parameters":["forumTopicComment"],"bindings":{"forumTopicComment":"ID"}},"forum-topic.create":{"uri":"forums\/forum\/{forum}\/topic\/create","methods":["GET"],"parameters":["forum"],"bindings":{"forum":"ID"}},"demo":{"uri":"demo","methods":["GET"]},"home":{"uri":"\/","methods":["GET"]},"tickets.index":{"uri":"tickets","methods":["GET"]},"ranking.beaten-games":{"uri":"ranking\/beaten-games","methods":["GET"]},"message-thread.index":{"uri":"messages","methods":["GET"]},"message.create":{"uri":"messages\/create","methods":["GET"]},"achievement.tickets":{"uri":"achievement\/{achievement}\/tickets","methods":["GET"],"parameters":["achievement"],"bindings":{"achievement":"ID"}},"achievement.create-ticket":{"uri":"achievement\/{achievement}\/tickets\/create","methods":["GET"],"parameters":["achievement"],"bindings":{"achievement":"ID"}},"achievement.report-issue":{"uri":"achievement\/{achievement}\/report-issue","methods":["GET"],"parameters":["achievement"],"bindings":{"achievement":"ID"}},"achievement.comments":{"uri":"achievement\/{achievement}\/comments","methods":["GET"],"parameters":["achievement"],"bindings":{"achievement":"ID"}},"redirect":{"uri":"redirect","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"developer.tickets":{"uri":"user\/{user}\/tickets","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"reporter.tickets":{"uri":"user\/{user}\/tickets\/feedback","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"developer.tickets.resolved-for-others":{"uri":"user\/{user}\/tickets\/resolved-for-others","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"developer.feed":{"uri":"user\/{user}\/developer\/feed","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"developer.claims":{"uri":"user\/{user}\/developer\/claims","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"developer.sets":{"uri":"user\/{user}\/developer\/sets","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"user.comments":{"uri":"user\/{user}\/comments","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"user.posts":{"uri":"user\/{user}\/posts","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"user.game.activity":{"uri":"user\/{user}\/game\/{game}\/activity","methods":["GET"],"parameters":["user","game"],"bindings":{"user":"User","game":"ID"}},"game.compare-unlocks":{"uri":"user\/{user}\/game\/{game}\/compare","methods":["GET"],"parameters":["user","game"],"bindings":{"user":"User","game":"ID"}},"user.moderation-comments":{"uri":"user\/{user}\/moderation-comments","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"user.completion-progress":{"uri":"user\/{user}\/progress","methods":["GET"],"parameters":["user"],"bindings":{"user":"User"}},"leaderboard.comments":{"uri":"leaderboard\/{leaderboard}\/comments","methods":["GET"],"parameters":["leaderboard"],"bindings":{"leaderboard":"ID"}},"game.random":{"uri":"game\/random","methods":["GET"]},"game.hash":{"uri":"game\/{game}\/hashes","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.hash.manage":{"uri":"game\/{game}\/hashes\/manage","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.hashes.comments":{"uri":"game\/{game}\/hashes\/comments","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.tickets":{"uri":"game\/{game}\/tickets","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.comments":{"uri":"game\/{game}\/comments","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.dev-interest":{"uri":"game\/{game}\/dev-interest","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.modification-comments":{"uri":"game\/{game}\/modification-comments","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.suggest":{"uri":"game\/{game}\/suggest","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.claims":{"uri":"game\/{game}\/claims","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"game.claims.comments":{"uri":"game\/{game}\/claims\/comments","methods":["GET"],"parameters":["game"],"bindings":{"game":"ID"}},"system.game.index":{"uri":"system\/{system}\/games","methods":["GET"],"parameters":["system"],"bindings":{"system":"ID"}},"rss.index":{"uri":"rss","methods":["GET"]},"ticket.show":{"uri":"ticket\/{ticket}","methods":["GET"],"parameters":["ticket"],"bindings":{"ticket":"ID"}},"message-thread.show":{"uri":"message-thread\/{messageThread}","methods":["GET"],"parameters":["messageThread"],"bindings":{"messageThread":"id"}},"terms":{"uri":"terms","methods":["GET"]},"games.suggest":{"uri":"games\/suggest","methods":["GET"]},"contact":{"uri":"contact","methods":["GET"]},"claims.index":{"uri":"claims","methods":["GET"]},"claims.expiring":{"uri":"claims\/expiring","methods":["GET"]},"claims.completed":{"uri":"claims\/completed","methods":["GET"]},"claims.active":{"uri":"claims\/active","methods":["GET"]},"game-hash.update":{"uri":"game-hash\/{gameHash}","methods":["PUT","PATCH"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}"},"parameters":["gameHash"],"bindings":{"gameHash":"hash"}},"game-hash.destroy":{"uri":"game-hash\/{gameHash}","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}"},"parameters":["gameHash"],"bindings":{"gameHash":"hash"}},"player.games.resettable":{"uri":"games\/resettable","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}"}},"player.game.achievements.resettable":{"uri":"game\/{game}\/achievements\/resettable","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}"},"parameters":["game"],"bindings":{"game":"ID"}},"user.game.destroy":{"uri":"user\/game\/{game}","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}"},"parameters":["game"],"bindings":{"game":"ID"}},"user.achievement.destroy":{"uri":"user\/achievement\/{achievement}","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}"},"parameters":["achievement"],"bindings":{"achievement":"ID"}},"forum.recent-posts":{"uri":"forums\/recent-posts","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"}},"user.comment.destroyAll":{"uri":"user\/{user}\/comments","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"},"parameters":["user"],"bindings":{"user":"User"}},"message.store":{"uri":"message","methods":["POST"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"}},"message-thread.destroy":{"uri":"message-thread\/{messageThread}","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"},"parameters":["messageThread"],"bindings":{"messageThread":"id"}},"login":{"uri":"login","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"}},"logout":{"uri":"logout","methods":["POST"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"}},"password.confirmation":{"uri":"auth\/password\/confirmed-status","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"}},"password.confirm":{"uri":"auth\/password\/confirm","methods":["POST"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}"}},"download.index":{"uri":"download.php","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"game.index":{"uri":"gameList.php","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"user.show":{"uri":"user\/{user}","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"},"parameters":["user"]},"achievement.show":{"uri":"achievement\/{achievement}{slug?}","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"},"parameters":["achievement","slug"]},"game.show":{"uri":"game\/{game}{slug?}","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"},"parameters":["game","slug"]},"leaderboard.show":{"uri":"leaderboard\/{leaderboard}{slug?}","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"},"parameters":["leaderboard","slug"]},"user.permalink":{"uri":"u\/{hashId}","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"},"parameters":["hashId"]},"settings.show":{"uri":"settings","methods":["GET","HEAD"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"settings.profile.update":{"uri":"settings\/profile","methods":["PUT"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"settings.preferences.update":{"uri":"settings\/preferences","methods":["PUT"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"settings.password.update":{"uri":"settings\/password","methods":["PUT"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"settings.email.update":{"uri":"settings\/email","methods":["PUT"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"settings.keys.web.destroy":{"uri":"settings\/keys\/web","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"settings.keys.connect.destroy":{"uri":"settings\/keys\/connect","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"user.delete-request.store":{"uri":"user\/delete-request","methods":["POST"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"user.delete-request.destroy":{"uri":"user\/delete-request","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"user.avatar.store":{"uri":"user\/avatar","methods":["POST"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}},"user.avatar.destroy":{"uri":"user\/avatar","methods":["DELETE"],"wheres":{"achievement":"[0-9]{1,17}","game":"[0-9]{1,17}","game_hash":"[a-zA-Z0-9]{1,32}","system":"[0-9]{1,17}","topic":"[0-9]{1,17}","forum":"[0-9]{1,17}","category":"[0-9]{1,17}","comment":"[0-9]{1,17}","news":"[0-9]{1,17}","slug":"-[a-zA-Z0-9_-]+","user":"[a-zA-Z0-9_]{1,20}"}}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
