<?php
namespace miniGameUi;

use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use minigameapi\Game;
use minigameapi\MiniGameApi;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

final class MiniGameUi extends PluginBase {
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        $form = FormAPI::getInstance()->createSimpleForm(function (Player $player, array $data) : void{
            $game = MiniGameApi::getInstance()->getGameManager()->getGame($data);
            if ($game->isRunning()) {
                MiniGameApi::getInstance()->getLanguage()->translateString('join.failed.alreadyStarted');
                return;
            }
            $game->addWaitingPlayer($player);
        });
        $form->setTitle('MiniGames !!');
        foreach (MiniGameApi::getInstance()->getGameManager()->getGames() as $game) {
            if (!$game instanceof Game) continue;
            if ($game->isRunning()) {
                $text = TextFormat::RED;
            } else {
                $text = TextFormat::GREEN;
            }
            $text .= $game->getName() . TextFormat::GRAY . TextFormat::EOL;
            if ($game->isRunning() or $game->isWaiting()) {
                $text .= MiniGameApi::getInstance()->getLanguage()->translateString('left.time',[(int)($game->isRunning() ? $game->getRemainingWaitTime() : $game->getRemainingWaitTime())]);
            } else {
                $text .= MiniGameApi::getInstance()->getLanguage()->translateString('left.players', [$game->getNeededPlayers(), count($game->getPlayers()), $game->getMaxPlayers()]);
            }
            $text .= TextFormat::EOL . $game->getDescription();
            $form->addButton($text,SimpleForm::IMAGE_TYPE_PATH,$game->getIconImage(), $game->getName());
        }
        $form->addButton('close',-1,'', 'quit');
        $form->sendToPlayer($sender);
        return true;
    }
}