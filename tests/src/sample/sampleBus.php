<?php
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;
use predaddy\messagehandling\DefaultFunctionDescriptorFactory;
use predaddy\messagehandling\SimpleMessageBus;

return new SimpleMessageBus(
    new AnnotatedMessageHandlerDescriptorFactory(new DefaultFunctionDescriptorFactory())
);
