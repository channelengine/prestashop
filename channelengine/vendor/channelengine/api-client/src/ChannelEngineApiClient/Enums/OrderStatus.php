<?php

namespace ChannelEngineApiClient\Enums {
	
    class OrderStatus {
		
        const IN_PROGRESS = 0;
        const SHIPPED = 1;
        const IN_BACKORDER = 2;
        const CANCELED = 3;
        const MANCO = 4;
        const IN_COMBI = 5; // combi: one or more not closed
        const CLOSED = 6; // combi:closed
        const NEW_ORDER = 7; // renamed NEW to NEW_ORDER because new is a protected keyword in php
		
    }	
}