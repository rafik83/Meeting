<?php

namespace Proximum\Vimeet\Domain\Intention;

final class IntentionType
{
    public const INTENTION_CANCEL_ORDERS = 'cancel_orders';
    public const INTENTION_REMOVE_CUSTOMIZED_MAIL = 'remove_customized_mail';
    public const INTENTION_REMOVE_STATIC_FORMULATION = 'remove_static_formulation';
    public const INTENTION_REMOVE_ADMIN = 'remove_admin';
    public const INTENTION_REMOVE_STAY = 'remove_stay';
    public const INTENTION_REMOVE_PROMOTION_CODE_FROM_ORDER = 'remove_promotion_code_from_order';
}
