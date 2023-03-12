class com.clubpenguin.tower.enemies.types.YellowBoss extends com.clubpenguin.tower.enemies.types.AbstractEnemy
{
    var getID, setView;
    function YellowBoss(scope, position, waypoints, $health, $speed, $dropsUpgrade)
    {
        super(position, waypoints, $health, $speed, $dropsUpgrade);
        this.setView(new com.clubpenguin.tower.enemies.views.YellowBossView(scope, this.getID()));
    } // End of the function
    var MOVE_DISTANCE = 4;
} // End of Class
