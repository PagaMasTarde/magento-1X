# Configuration

## :house: Access

To access to Paga+Tarde admin panel, we need to open the Magento admin panel and follow the next steps:

1 – System => Configuration
![Step 1](./configuration-step1.png?raw=true "Step 1")

2 – Scroll down and search the section SALES => Payment Methods
![Step 2](./configuration-step2.png?raw=true "Step 2")

3 – Scroll down to find the Paga+Tarde Payment Method and fill the fields with your configuration.
![Step 3](./configuration-step3.png?raw=true "Step 3")

## :clipboard: Options
In Paga+tarde admin panel, we can set the following options:

| Field &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Description<br/><br/>
| :------------- |:-------------| 
| Enabled      | <ul><li><b>Yes</b> => Module enabled</li><li><b>No</b> => Módule disabled (Default option)</li></ul>
| TEST Public API Key |  String you can get from your [Paga+Tarde profile](https://bo.pagamastarde.com/shop) when use a testing environment.
| TEST Private API Key |  String you can get from your [Paga+Tarde profile](https://bo.pagamastarde.com/shop) when use a testing environment.
| PROD Public API Key |  String you can get from your [Paga+Tarde profile](https://bo.pagamastarde.com/shop) when use a production environment.
| PROD Private API Key |  String you can get from your [Paga+Tarde profile](https://bo.pagamastarde.com/shop) when use a production environment. 
| How to open the payment |  <ul><li><b>Iframe</b> => After checkout, the user will watch a pop-up with Paga+Tarde side to fill the form without leave the current page</li><li><b>Redirect</b> => After checkout, the user will be redirected to Paga+Tarde side to fill the form. (Default and recommended option).</li></ul> 
| Product Simulator    |  Choose if we want to use installments simulator inside product page, in positive case, you can chose the simulator type. Recommended option: MINI
| Checkout Simulator  |   Choose if we want to use installments simulator inside checkout page, in positive case, you can chose the simulator type. Recommended option: MINI
| Number of default installments | Number of installments by default to use in simulator
| Maximum number of installments  | Maximum number of installments by default to use in simulator   
| Minimum cart amount (ex: 24.99) | Minimum amount to use the module and show the payment method to checkout       
| PaymentMethod Oder      | If you have more than one payment methods, you can chose the priority order of Paga+Tarde between them (by default the first).
| Checkout Pay Option Title | Checkout payment method title shown inside the box of the payment method option. By default in: "Financiación instantánea - 100% online"
| Checkout Progress Title | String shown in the checkout summary panel when you chose Paga+Tarde payment method
| Success Url | Location where user will be redirected after a succesful payment. This string will be concatenated to the base url to build the full url
| Failure Url | Location where user will be redirected after a wrong payment. This string will be concatenated to the base url to build the full url 
