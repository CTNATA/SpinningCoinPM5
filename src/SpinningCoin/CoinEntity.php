<?php



declare(strict_types=1);



namespace SpinningCoin;



use pocketmine\utils\TextFormat;

use onebone\economyapi\EconomyAPI;

use pocketmine\entity\Human;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\Server;

use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;

use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;



class CoinEntity extends Human {



    public function getEconomyAPI(): EconomyAPI {

        /** @var EconomyAPI $economy */

        $economy = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");

        return $economy;

    }



    public function onUpdate(int $currentTick): bool {

        $this->location->yaw += 5.5;

        $this->move($this->motion->x, $this->motion->y, $this->motion->z);

        $this->updateMovement();



        if (class_exists(EconomyAPI::class) && class_exists(Main::class)) {

            foreach ($this->getViewers() as $viewer) {

                $economy = $this->getEconomyAPI();

                $coins = $economy->myMoney($viewer);



                //negro el que lo lea 

                $this->sendData([$viewer], [EntityMetadataProperties::NAMETAG => new StringMetadataProperty(TextFormat::colorize(str_replace(["{coins}", "{player}"], [

                    $coins,

                    $viewer->getName()

                ], $this->getNameTag())))]);

            }

        } else {

            $this->flagForDespawn();

        }

        return parent::onUpdate($currentTick);

    }



    public function attack(EntityDamageEvent $source): void {

        $source->cancel();

    }

}


