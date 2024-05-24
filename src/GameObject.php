<?php

namespace Deminer;

class GameObject
{
    protected RenderType $renderType;
    protected ?Collision $collision = null;

    public function onClick(ClickEvent $event): void
    {
    }

    public function getRenderType(): RenderType
    {
        return $this->renderType;
    }

    public function getCollision(): ?Collision
    {
        return $this->collision;
    }

    public function isCollidable(): bool
    {
        return $this->collision !== null;
    }
}