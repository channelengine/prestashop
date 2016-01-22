<?php
namespace ChannelEngineApiClient\Enums {
	
    class ReturnReason {
        const PRODUCT_DEFECT = 0;
        const PRODUCT_UNSATISFACTORY = 1;
        const REFUSED = 4;
        const REFUSED_DAMAGED = 5;
        const  WRONG_ADDRESS = 6;
        const NOT_COLLECTED = 7;
        const OTHER = 99;
    }
	
}