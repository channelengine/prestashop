<?php
	
	// When not using other frameworks, use this loader to autoload the namepaces.
	//require_once __DIR__ . '/../loader.php';

	// Autoload files using Composer autoload
	require_once __DIR__ . '/../vendor/autoload.php'; 

	// Import the required namespaces
	use ChannelEngineApiClient\Client\ApiClient;
	
	use ChannelEngineApiClient\Helpers\Collection;
	use ChannelEngineApiClient\Helpers\JsonMapper;
	
	use ChannelEngineApiClient\Models\Address;
	use ChannelEngineApiClient\Models\Cancellation;
	use ChannelEngineApiClient\Models\CancellationLine;
	use ChannelEngineApiClient\Models\Message;
	use ChannelEngineApiClient\Models\Order;
	use ChannelEngineApiClient\Models\OrderLine;
	use ChannelEngineApiClient\Models\OrderExtraDataItem;
	use ChannelEngineApiClient\Models\Product;
	use ChannelEngineApiClient\Models\ReturnObject;
	use ChannelEngineApiClient\Models\ReturnLine;
	use ChannelEngineApiClient\Models\Shipment;
	use ChannelEngineApiClient\Models\ShipmentLine;
	
	use ChannelEngineApiClient\Enums\CancellationLineStatus;
	use ChannelEngineApiClient\Enums\CancellationStatus;
	use ChannelEngineApiClient\Enums\Gender;
	use ChannelEngineApiClient\Enums\MancoReason;
	use ChannelEngineApiClient\Enums\OrderStatus;
	use ChannelEngineApiClient\Enums\ReturnReason;
	use ChannelEngineApiClient\Enums\ReturnStatus;
	use ChannelEngineApiClient\Enums\ReturnAcceptStatus;
	use ChannelEngineApiClient\Enums\ShipmentLineStatus;
	use ChannelEngineApiClient\Enums\ShipmentStatus;
	
	class Examples {

		private $client;

		public function __construct() {
			// Instantiate the client with your personal api key and api secret.
			$this->client = new ApiClient('64c99c1c512a68be177f173371c2243356431e42', 'e6c2cf76bc681a61e53682fc2d01640731dc7843', 'yourstore');
		}

		public function handleRequest() {
			// Run the examples
			$this->getOrdersExample();
			//$this->getReturnsExample();

			//$this->getStatisticsExample();

			// The following examples require real data to work.
			// More information has been specified within the functions.
			//$this->updateReturnExample();
			//$this->postShipmentExample();
			//$this->postReturnExample();
			//$this->getProductsExample();
		}

		private function getOrdersExample() {
			try {
				// Fetch order information from ChannelEngine

				// Example 1: Get all open orders.
				// $orders = $this->client->getOrders();

				// Example 2: Get all orders with the given status
				$orders = $this->client->getOrders(
					array(
						OrderStatus::IN_PROGRESS
					)
				);
				
				
				// Example 3: Get all orders with the given statuses and date range
				/*
				$orders = $this->client->getOrders(
					array(
						OrderStatus::IN_PROGRESS,
						OrderStatus::IN_BACKORDER
					),
					new DateTime('-1 year'),
					new DateTime('-1 week')
				);
				*/

				// Print the orders
				$this->printOrders($orders);
			} catch(Exception $e) {
				// Print the exception
				$this->printError($e->getMessage());	
			}
		}

		private function getReturnsExample() {
			try {
				// Some channels handle customer contact and the return procedure for you.
				// In this case the return will be communicated to ChannelEngine.
				// These returns can be fetched from ChannelEngine.

				// Example 1: Get all declared returns
				// $returns = $this->client->getReturns();

				// Example 2: Get all returns with the given statuses and date
				$returns = $this->client->getReturns(
					array(
						ReturnStatus::CLOSED
					),
					new DateTime('-1 year')
				);
				

				// Example 3: Get all returns with the given statuses and date range
				/*
				$returns = $this->client->getReturns(
					array(
						ReturnStatus::DECLARED,
						ReturnStatus::RECEIVED
					),
					new DateTime('-1 year'),
					new DateTime('-1 week')
				);
				*/

				// Print the returns
				$this->printReturns($returns);
			} catch(Exception $e) {
				// Print the exception
				$this->printError($e->getMessage());	
			}
		}


		private function updateReturnExample() {
			try {
				// Some channels handle customer contact and the return procedure for you.
				// In this case the return will be communicated to ChannelEngine.
				// The status needs to be updated in ChannelEngine when you have received
				// the returned product.

				// Note: Use an existing return with lines here
				$return = new ReturnObject();
				$returnLines = $return->getLines();

				// Note: Act as if we have a real order with 1 line
				$returnLine = isset($returnLines[0]) ? $returnLines[0] : null;
				if($returnLine != null) {

					// Example 1: Mark all products as received
					$returnLine->setAcceptedQuantity($returnLine->getQuantity());
					$return->setStatus(ReturnStatus::RECEIVED);

					// Example 2: Update the return with some extra information
					$return->setMerchantReturnNo('your-return-number');
					$return->setMerchantComment('Product was dead on arrival');
					
					// Send it to ChannelEngine
					$return = $this->client->putReturn($return);
				}
			} catch(Exception $e) {
				// Print the exception
				$this->printError($e->getMessage());	
			}
		}

		private function postShipmentExample() {
			try {
				// Marking orders as shipped, partially shipped or not shipped is done
				// by posting shipments.

				// Note: Use data from an existing order here
				$orderToShip = new Order();
				$orderLinesToShip = $orderToShip->getLines();
				
				// Note: Act as if we have a real order with 1 line
				$orderLineToShip = isset($orderLinesToShip[0]) ? $orderLinesToShip[0] : null;
				if($orderLineToShip != null) {

					// Create the shipment
					$shipment = new Shipment();
					$shipment->setOrderId( $orderToShip->getId() );
					$shipment->setMerchantShipmentNo('your-shipment-number');
					$shipment->setTrackTraceNo('3SABC123456789');
					$shipment->setMethod('postNL');
					
					$shipmentLine = new ShipmentLine();
					$shipmentLine->setOrderLineId( $orderLineToShip->getId() );

					// We will subtract the cancelled quantity because the customer may have
					// cancelled part of the orderline before we fetched the order.
					// If we are only able to ship part of the orderline,
					// we will specify the shipmentlines for both the shipped and unshipped products (see example 2 & 3)
					$shipmentLine->setQuantity( $orderLineToShip->getQuantity() - $orderLineToShip->getCancelledQuantity() ); 	
					
					// Example 1: Mark the given quantity of products as shipped. 		
					$shipmentLine->setStatus( ShipmentLineStatus::SHIPPED );

					// Example 2: Mark the given quantity of products as manco.			
					$shipmentLine->setStatus( ShipmentLineStatus::MANCO );
					$shipment->setMancoReason( MancoReason::DAMAGED );
					$shipment->setMancoComment( 'Product damaged during order picking.' );

					// Example 3: Mark the given quantity of products as being in backorder.
					// Set the date on which we expect the customer to receive the product.			
					$shipmentLine->setStatus( ShipmentLineStatus::IN_BACKORDER );
					$shipmentLine->setExpectedDate(new DateTime('+2 weeks'));

					// Add the shipment line and send it to ChannelEngine
					$shipmentLines = $shipment->getLines();
					$shipmentLines[] = $shipmentLine;
					$shipment->setLines($shipmentLines);

					$shipment = $this->client->postShipment($shipment);
				}
			} catch(Exception $e) {
				// Print the exception
				$this->printError($e->getMessage());	
			}
		}

		private function postReturnExample() {
			try {
				// Some channels expect you to handle customer contact and the
				// return procedure of products yourself. In this case the return needs
				// to be registered to ChannelEngine.

				// The shipment that needs to be registered as being returned by the customer.
				// Note: Use an existing shipment with lines here.
				$shipment = new Shipment();
				$shipmentLines = $shipment->getLines();

				// Note: Act as if we have a real shipment with 1 line
				$shipmentLine = isset($shipmentLines[0]) ? $shipmentLines[0] : null;
				if($shipmentLine != null) {

					// Create the return
					$return = new ReturnObject();
					$return->setShipmentId($shipment->getId());
					$return->setMerchantReturnNo('your-return-number');
					$return->setMerchantComment('Product was dead on arrival');
					$return->setStatus(ReturnStatus::DECLARED);
					$return->setReason(ReturnReason::PRODUCT_DEFECT);
					$return->setLines(new Collection('ChannelEngineApiClient\Models\ReturnLine'));
					
					// Register the shipment line and amount of products being returned
					$returnLine = new ReturnLine();
					$returnLine->setShipmentLineId($shipmentLine->getId());
					$returnLine->setQuantity(1);
					
					// Add the return line and send it to ChannelEngine
					$returnLines = $return->getLines();
					$returnLines[] = $returnLine;
					$returnLines = $return->setLines($returnLines);

					$return = $this->client->postReturn($return);
				}

			} catch(Exception $e) {
				// Print the exception
				$this->printError($e->getMessage());	
			}
		}

		private function getProductsExample() {
			try {

				// Example 1: Search products by GTIN (EAN)
				$products = $this->client->getProducts('8714302109119');
				$products($this->printProducts($products));

				// Example 2: Get a product by ID
				$product = $this->client->getProduct(1);

				// Example 3: Update a product
				$product->setPrice(110.95);
				$this->client->putProduct($product);

				// Example 4: Sync product data
				$product1 = new Product();
				$product1->setEan('8714302109119');
				$product1->setMerchantProductNo('10785');
				$product1->setPrice(200);

				$product2 = new Product();
				$product2->setEan('086786257951');
				$product2->setMerchantProductNo('10799');
				$product2->setPrice(240);

				$products = array($product1, $product2);

			} catch(Exception $e) {
				// Print the exception
				$this->printError($e->getMessage());	
			}
		}

		private function getStatisticsExample() {
			try {
				// Example 1: Get revenue totals.
				/*$revenue = $this->client->getStatisticsRevenue(
					new DateTime('-1 year'),
					new DateTime('+1 day')
				);*/

				// Example 2: Get click conversion rates.
				$clickConversion = $this->client->getStatisticsClickConversion(
					new DateTime('-1 year'),
					new DateTime('+1 day')
				);
				
				// Example 3: Get order counts
				/*$orders = $this->client->getStatisticsOrders(
					new DateTime('-1 week'),
					new DateTime('+1 day')
				);*/

				print_r($clickConversion);
			} catch(Exception $e) {
				// Print the exception
				$this->printError($e->getMessage());	
			}
		}

		// Note: All code beyond this comment is solely used to output the examples to the browser,
		// and is therefore not required to connect to ChannelEngine
		private function printDebug($data) {
			echo('<pre>');
			var_dump($data);
			echo('</pre>');
		}


		private function printError($message) {
			echo('<div style="color: #b94a48; border: 1px solid #eed3d7; background: #f2dede; margin: 5px; padding: 5px;">');
			echo('<p><strong>An error occured while making the request</strong></p>');
			echo('<p>The server returned the following message:</p>');
			echo('<pre>'.$message.'</pre>');
			echo('</div>');
		}
		
		private function printReturns($returns) {
			if(!is_null($returns)) {
				foreach($returns as $return) {
					?>
						<div style="margin: 5px; padding: 10px; width: 500px; float: left; background: #eee; color: #f00">
							<strong>Return</strong>
							<p>Status: <?=$return->getStatus();?></p>
							<p>Reason: <?=$return->getReason();?></p>
						</div>
					<?php
				}
				?>
				<div style="clear: both;"></div>
				<?php
			}	
		}

		private function printProducts($products) {
			if(!is_null($products)) {
				foreach($products as $product) {
					?>
						<div style="margin: 5px; padding: 10px; width: 500px; float: left; background: #eee;">
							<strong>Product</strong>
							<p>Name: <?=$product->getName();?></p>
							<div style="background: #fff; padding: 10px;">
								<?php
									foreach($product->getExtraData() as $ped)
									{
								?>
									<p><?=$ped->getKey();?>: <?=$ped->getValue();?></p>
								<?php
									}
								?>
							</div>
						</div>
					<?php
				}
				?><div style="clear: both;"></div><?php
				
			}
		}
		
		private function printOrders($orders) {
			if(!is_null($orders)) {
				foreach($orders as $order) {
					?>
						<div style="margin: 5px; padding: 10px; width: 500px; float: left; background: #eee;">
							<strong>Order</strong>
							<p>Status: <?=$order->getStatus();?></p>
							<p>Email: <?=$order->getEmail();?></p>
							<p>Date: <?=$order->getCreatedAt();?></p>
							<p>Name: <?=$order->getChannelName();?></p>
							<div style="background: #fff; padding: 10px;">
								<?php
									foreach($order->getLines() as $line)
									{
								?>
									<p><?=$line->getProductName();?></p>
								<?php
									}
								?>
							</div>
						</div>
					<?php
				}
				?><div style="clear: both;"></div><?php
				
			}
		}
	}

	$examples = new Examples();
	$examples->handleRequest();
?>