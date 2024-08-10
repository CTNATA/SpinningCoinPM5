<?php



declare(strict_types=1);



namespace SpinningCoin;



use JsonException;

use pocketmine\world\World;

use pocketmine\entity\Skin;

use pocketmine\entity\Human;

use pocketmine\player\Player;

use pocketmine\command\Command;

use pocketmine\entity\EntityFactory;

use pocketmine\command\CommandSender;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\plugin\PluginBase;

use pocketmine\entity\EntityDataHelper;



class Main extends PluginBase {



    protected function onEnable(): void {

        EntityFactory::getInstance()->register(CoinEntity::class, function (World $world, CompoundTag $nbt): CoinEntity {

            return new CoinEntity(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);

        }, ['CoinEntity']);

        $this->saveDefaultConfig();

    }



    /**

     * @throws JsonException

     */

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if ($command->getName() === "spinningcoin") {

            if ($sender instanceof Player) {

                if ($sender->getServer()->isOp($sender->getName())) {

                    if (!isset($args[0])) {

                        $sender->sendMessage("§aUsage: /spinningcoin spawn|remove");

                        return false;

                    }

                    if ($args[0] === "spawn") {

                        $this->spawnCoin($sender);

                        $sender->sendMessage("§bSpinning coin spawned.");

                    } elseif ($args[0] === "remove") {

                        $coinEntity = $this->getNearSpinningCoin($sender);



                        if ($coinEntity !== null) {

                            $coinEntity->flagForDespawn();



                            $sender->sendMessage("§bSpinning coin removed.");

                            return true;

                        }

                        $sender->sendMessage("§cNo spinning coin found.");

                    }

                }

            }

        }

        return true;

    }



    public function getNearSpinningCoin(Player $player): ?CoinEntity {

        $level = $player->getWorld();



        foreach ($level->getEntities() as $entity) {

            if ($entity instanceof CoinEntity) {

                if ($player->getPosition()->distance($entity->getPosition()) <= 5 && $entity->getPosition()->distance($player->getPosition()) > 0) {

                    return $entity;

                }

            }

        }

        return null;

    }



    /**

     * @throws JsonException

     */

    public function spawnCoin(Player $sender): void {

        $path = $this->getFile() . "resources/texture.png";

        $img = @imagecreatefrompng($path);

        $skinBytes = "";

        $s = (int)@getimagesize($path)[1];



        for ($y = 0; $y < $s; $y++) {

            for ($x = 0; $x < 64; $x++) {

                $color = @imagecolorat($img, $x, $y);

                $a = ((~($color >> 24)) << 1) & 0xff;

                $r = ($color >> 16) & 0xff;

                $g = ($color >> 8) & 0xff;

                $b = $color & 0xff;

                $skinBytes .= chr($r) . chr($g) . chr($b) . chr($a);

            }

        }

        @imagedestroy($img);



        $skin = new Skin($sender->getSkin()->getSkinId(), $skinBytes, '', 'geometry.geometry.coin', file_get_contents($this->getFile() . 'resources/Coin.geo.json'));

        $entity = new CoinEntity($sender->getLocation(), $skin);

        $nameTag = $this->getConfig()->get("nametag", "&bYou have &e{coins} coins, &a{player}");

        $entity->setNameTag($nameTag);

        $entity->setNameTagAlwaysVisible();

        $entity->setNameTagVisible();

        $entity->spawnToAll();

    }

}




