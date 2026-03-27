<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Entity;

use Abderrahim\SyliusPopupPlugin\Enum\PopupStyle;
use Abderrahim\SyliusPopupPlugin\Enum\ShowFrequency;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Abderrahim\SyliusPopupPlugin\Enum\TriggerType;
use Sylius\Resource\Model\ResourceInterface;

interface PopupCampaignInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getTitle(): ?string;

    public function setTitle(?string $title): void;

    public function getBody(): ?string;

    public function setBody(?string $body): void;

    public function getCtaLabel(): ?string;

    public function setCtaLabel(?string $ctaLabel): void;

    public function getCtaUrl(): ?string;

    public function setCtaUrl(?string $ctaUrl): void;

    public function getDiscountCode(): ?string;

    public function setDiscountCode(?string $discountCode): void;

    public function isEmailCaptureEnabled(): bool;

    public function setEmailCaptureEnabled(bool $emailCaptureEnabled): void;

    public function getStyle(): PopupStyle;

    public function setStyle(PopupStyle $style): void;

    public function getBackgroundColor(): string;

    public function setBackgroundColor(string $backgroundColor): void;

    public function getTextColor(): string;

    public function setTextColor(string $textColor): void;

    public function getButtonColor(): string;

    public function setButtonColor(string $buttonColor): void;

    public function getTriggerType(): TriggerType;

    public function setTriggerType(TriggerType $triggerType): void;

    public function getTriggerDelay(): int;

    public function setTriggerDelay(int $triggerDelay): void;

    public function getTriggerScrollDepth(): int;

    public function setTriggerScrollDepth(int $triggerScrollDepth): void;

    public function getShowFrequency(): ShowFrequency;

    public function setShowFrequency(ShowFrequency $showFrequency): void;

    public function getTargetPages(): TargetPages;

    public function setTargetPages(TargetPages $targetPages): void;

    public function getTargetAudience(): TargetAudience;

    public function setTargetAudience(TargetAudience $targetAudience): void;

    public function getPriority(): int;

    public function setPriority(int $priority): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function getUpdatedAt(): ?\DateTimeInterface;
}
