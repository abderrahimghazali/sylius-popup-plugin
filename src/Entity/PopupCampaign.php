<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Entity;

use Abderrahim\SyliusPopupPlugin\Enum\PopupStyle;
use Abderrahim\SyliusPopupPlugin\Enum\ShowFrequency;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Abderrahim\SyliusPopupPlugin\Enum\TriggerType;
use Sylius\Resource\Model\TimestampableTrait;

class PopupCampaign implements PopupCampaignInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected ?string $name = null;

    protected bool $enabled = false;

    protected ?string $title = null;

    protected ?string $body = null;

    protected ?string $ctaLabel = null;

    protected ?string $ctaUrl = null;

    protected ?string $discountCode = null;

    protected bool $emailCaptureEnabled = false;

    protected PopupStyle $style = PopupStyle::Modal;

    protected string $backgroundColor = '#ffffff';

    protected string $textColor = '#111111';

    protected string $buttonColor = '#000000';

    protected TriggerType $triggerType = TriggerType::ExitIntent;

    protected int $triggerDelay = 5;

    protected int $triggerScrollDepth = 50;

    protected ShowFrequency $showFrequency = ShowFrequency::Session;

    protected TargetPages $targetPages = TargetPages::All;

    protected TargetAudience $targetAudience = TargetAudience::Everyone;

    protected int $priority = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getCtaLabel(): ?string
    {
        return $this->ctaLabel;
    }

    public function setCtaLabel(?string $ctaLabel): void
    {
        $this->ctaLabel = $ctaLabel;
    }

    public function getCtaUrl(): ?string
    {
        return $this->ctaUrl;
    }

    public function setCtaUrl(?string $ctaUrl): void
    {
        $this->ctaUrl = $ctaUrl;
    }

    public function getDiscountCode(): ?string
    {
        return $this->discountCode;
    }

    public function setDiscountCode(?string $discountCode): void
    {
        $this->discountCode = $discountCode;
    }

    public function isEmailCaptureEnabled(): bool
    {
        return $this->emailCaptureEnabled;
    }

    public function setEmailCaptureEnabled(bool $emailCaptureEnabled): void
    {
        $this->emailCaptureEnabled = $emailCaptureEnabled;
    }

    public function getStyle(): PopupStyle
    {
        return $this->style;
    }

    public function setStyle(PopupStyle $style): void
    {
        $this->style = $style;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    public function getTextColor(): string
    {
        return $this->textColor;
    }

    public function setTextColor(string $textColor): void
    {
        $this->textColor = $textColor;
    }

    public function getButtonColor(): string
    {
        return $this->buttonColor;
    }

    public function setButtonColor(string $buttonColor): void
    {
        $this->buttonColor = $buttonColor;
    }

    public function getTriggerType(): TriggerType
    {
        return $this->triggerType;
    }

    public function setTriggerType(TriggerType $triggerType): void
    {
        $this->triggerType = $triggerType;
    }

    public function getTriggerDelay(): int
    {
        return $this->triggerDelay;
    }

    public function setTriggerDelay(int $triggerDelay): void
    {
        $this->triggerDelay = $triggerDelay;
    }

    public function getTriggerScrollDepth(): int
    {
        return $this->triggerScrollDepth;
    }

    public function setTriggerScrollDepth(int $triggerScrollDepth): void
    {
        $this->triggerScrollDepth = $triggerScrollDepth;
    }

    public function getShowFrequency(): ShowFrequency
    {
        return $this->showFrequency;
    }

    public function setShowFrequency(ShowFrequency $showFrequency): void
    {
        $this->showFrequency = $showFrequency;
    }

    public function getTargetPages(): TargetPages
    {
        return $this->targetPages;
    }

    public function setTargetPages(TargetPages $targetPages): void
    {
        $this->targetPages = $targetPages;
    }

    public function getTargetAudience(): TargetAudience
    {
        return $this->targetAudience;
    }

    public function setTargetAudience(TargetAudience $targetAudience): void
    {
        $this->targetAudience = $targetAudience;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }
}
