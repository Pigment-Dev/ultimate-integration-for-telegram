<?php
# @Author: amirhp-com <its@amirhp.com>
# @Last modified by:   amirhp-com <its@amirhp.com>
# @Last modified time: 2022/10/13 00:11:39
namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
class StartCommand extends SystemCommand
{
  protected $name = 'start';
  protected $description = 'Start command';
  protected $usage = '/start';
  protected $version = '1.2.0';
  protected $private_only = true;
  public function execute(): ServerResponse
  {
    // If you use deep-linking, get the parameter like this: @see https://core.telegram.org/bots#deep-linking
    $cmdline = $this->getMessage()->getText(true);
    $chat_id = $this->getMessage()->getChat()->getId();
    $msg = "Hi there! 👋\n".
    "Welcome to *PeproDev Ultimate Invoice TelegramBot*\n\n".
    "_I will send you PDF Invoice of every WooCommerce Order created on my host site:_ 🌐 ".home_url()."\n\n".
    "_You can add me to any Group/Channel and I will send PDFs there, you just need to:\n\n".
    "1️⃣ Add me as an administrator\n2️⃣ Send /setup to get started_\n".
    "\n👨‍🔧 Support: @ahosseinhp\n👨‍🔧 Developer: [Amirhp-com](https://amirhp.com/landing)\n\n🆔 ChatID: `$chat_id`";
    return $this->replyToChat($msg, array(
      "parse_mode"               => "markdown",
      "reply_to_message_id"      => $this->getMessage()->getMessageId(),
      "protect_content"          => false,
      "disable_web_page_preview" => true,
    ));
  }
}
