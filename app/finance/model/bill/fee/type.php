<?php
class finance_mdl_bill_fee_type extends dbeav_model
{
    public function modifier_channel($row)
    {
        return finance_channel::$channel_name[$row];
    }
}