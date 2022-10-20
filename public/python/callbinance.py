import json, math, sys
from datetime import datetime
# from flask import Flask, request, jsonify, render_template
from binance.client import Client
from binance.exceptions import BinanceAPIException
from binance.enums import *
print ('Argument List:' + str(sys.argv))
print('Client Key = ' + str(sys.argv[1]))
# print('Client Secret = ' + str(sys.argv[2]))

client_key = sys.argv[1]
client_secret = sys.argv[2]
action = sys.argv[3]
stable_coin = sys.argv[4]
crypto_asset = sys.argv[5]
data =  json.dumps({
    'action': action,
    'stable_coin': stable_coin,
    'crypto_asset': crypto_asset,

})

# Max attempts to sent requests to Binance
max_attempts = 3

def round_decimals_down(number:float, decimals:int=2):
    """
    Returns a value rounded down to a specific number of decimal places.
    """
    if not isinstance(decimals, int):
        raise TypeError("decimal places must be an integer")
    elif decimals < 0:
        raise ValueError("decimal places has to be 0 or more")
    elif decimals == 0:
        return math.floor(number)

    factor = 10 ** decimals
    return math.floor(number * factor) / factor

def getCurrentOpenPositionAmount(client : Client):

    failed = True
    previous_position_amount = 0

    # Begin call for get Account Balance
    while failed == True:
        # Increment counter
        try:
            # Get all posible positions
            possible_positions = client.futures_account()['positions']
            current_open_position = [x for x in possible_positions if float(x['maintMargin']) != 0]
            if len(current_open_position) != 0:
                previous_position_amount = float(current_open_position[0]['positionAmt'])
            failed = False
        except BinanceAPIException as e:
            print('Function getCurrentOpenPositionAmount - ')
            print(e)
            failed = True
    return abs(round_decimals_down(previous_position_amount, 3))


def exitTrade(client : Client, symbol='BTCUSDT', side_to_cancel_position = SIDE_BUY):
    failed = True

    print('Exiting trade because of failed request')

    current_position_size = getCurrentOpenPositionAmount(client)

    if current_position_size != 0:
        # Begin call for open trade
        while failed == True:
            try:
                # Cancel all orders on new signal
                client.futures_create_order(symbol=symbol, side=side_to_cancel_position, type=ORDER_TYPE_MARKET, quantity=current_position_size)
                print('Position exited')
                failed = False
            except BinanceAPIException as e:
                print('Function exitTrade - ')
                print(e)
                failed = True
    else:
        print('No position to exit')


    # Reset globals
    failed = True

    # Begin call for cancel all orders before open any trade
    while failed == True:

        try:
            # Cancel all orders on new signal
            client.futures_cancel_all_open_orders(symbol=symbol)
            failed = False
        except BinanceAPIException as e:
            print('Function exitTrade cancell all open orders - ')
            print(e)
            failed = True

    print('TP / SL canceled')

def open_bot_position(client : Client, bot_side=SIDE_BUY, stable_coin='BUSD', crypto_asset='BTC', bot_leverage=5, bot_starting_position_percent=0.7, bot_tp_percent=40, bot_sl_percent=30):

    action = 'Opening' if bot_side == SIDE_BUY else 'Closing'

    print(action + ' position for pair: ' + crypto_asset + '/' + stable_coin)

    bot_symbol = crypto_asset + stable_coin

    # Defines the current attempts on the request to Binance and has flag is failed
    current_attempts = 0
    failed = True

    # Begin call to change leverage
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Change leverage
            client.futures_change_leverage(symbol=bot_symbol, leverage=bot_leverage)
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position change levarage - ')
            print(e)
            failed = True

    # Exit everything if failed
    if failed:
        exitTrade(client, bot_symbol, tp_sl_side)
        return False

    # Reset globals
    current_attempts = 0
    failed = True

    # Decide bot Short/Long
    tp_sl_side = SIDE_SELL if bot_side == SIDE_BUY else SIDE_BUY

    print('========================================')

    print('New signal recieved! Opening trade: ' + bot_side)

    account_balances = float(0)
    previous_position_amount = getCurrentOpenPositionAmount(client)
    print('Previous Position Ammount = ' + str(previous_position_amount))
    current_stable_coin_balance = 0

    # Begin call for get Account Balance
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Get acc balance
            # DEBUG HERE?
            account_balances = client.futures_account_balance()
            current_stable_coin_balance_array = [x for x in account_balances if x['asset'] == stable_coin]
            if len(current_stable_coin_balance_array) != 0:
                current_stable_coin_balance = round_decimals_down(float(current_stable_coin_balance_array[0]['balance']), 2)
            print('Current ' + stable_coin + ' balance: ' + str(current_stable_coin_balance))
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position account balance - ' + str(e))
            failed = True

    # Exit everything if failed
    if failed:
        exitTrade(client, bot_symbol, tp_sl_side)
        return False

    # Reset globals
    current_attempts = 0
    failed = True

    # Begin calculations such as
    # 1. Calculate quantity to open
    # 2. Calculate TP and SL

    # Begin call for get Price of the symbol
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Get Symbol Price
            symbol_price = float(client.get_symbol_ticker(symbol=bot_symbol)["price"])
            print('Symbol Price - ' + str(symbol_price))
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position price of symbol - ')
            print(e)
            failed = True

    # Exit everything if failed
    if failed:
        exitTrade(client, bot_symbol, tp_sl_side)
        return False

    # Reset globals
    current_attempts = 0
    failed = True

    if bot_side == SIDE_BUY:
        tp_price =  round_decimals_down(symbol_price + symbol_price * (bot_tp_percent / (bot_leverage * 100)), 2)
        sl_price = round_decimals_down(symbol_price - symbol_price * (bot_sl_percent / (bot_leverage * 100)), 2)
    else:
        tp_price =  round_decimals_down(symbol_price - symbol_price * (bot_tp_percent / (bot_leverage * 100)), 2)
        sl_price = round_decimals_down(symbol_price + symbol_price * (bot_sl_percent / (bot_leverage * 100)), 2)

    print("tp_price: " + str(tp_price))
    print("sl_price: " + str(sl_price))

    available_bot_balance_for_trade = round_decimals_down(current_stable_coin_balance * bot_starting_position_percent, 2)
    print('Available bot balance = ' + str(available_bot_balance_for_trade))

    available_bot_balance_for_trade = available_bot_balance_for_trade * bot_leverage
    print('Available bot balance + leverage = ' + str(available_bot_balance_for_trade))

    # print('BOT Opening position with quantity = ' + str(round_decimals_down(available_bot_balance_for_trade / symbol_price, 2)))
    # print('BOT Has position open with quantity = ' + str(previous_position_amount))
    bot_quantity = round_decimals_down(available_bot_balance_for_trade / symbol_price, 3)
    bot_quantity = abs(bot_quantity + previous_position_amount)
    print('Bot quantity ' + str(bot_quantity))
    print('========================================')

    # Begin call for cancel all orders before open any trade
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Cancel all orders on new signal
            client.futures_cancel_all_open_orders(symbol=bot_symbol)
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position cancel all orders before request - ')
            print(e)
            failed = True


    # Exit everything if failed
    if failed:
        exitTrade(client, bot_symbol, tp_sl_side)
        return False

    # Reset globals
    current_attempts = 0
    failed = True

    # Begin call for open trade
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Cancel all orders on new signal
            client.futures_create_order(symbol=bot_symbol, side=bot_side, type=ORDER_TYPE_MARKET, quantity=bot_quantity)
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position open position - ')
            print(e)
            failed = True


    # Exit everything if failed
    if failed:
        exitTrade(client, bot_symbol, tp_sl_side)
        return False

    # Reset globals
    current_attempts = 0
    failed = True

    # Begin call for tp open position

    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Take Profit
            client.futures_create_order(symbol=bot_symbol, side=tp_sl_side, type='TAKE_PROFIT_MARKET', closePosition=True, stopPrice=tp_price, workingType='MARK_PRICE')
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position tp - ')
            print(e)
            failed = True

    # Begin call for sl open position
    current_attempts = 0
    failed = True

    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Stop Loss
            # client.futures_create_order(symbol=bot_symbol, side=tp_sl_side, type='TRAILING_STOP_MARKET', quantity=bot_quantity, activationPrice=symbol_price, callbackRate=0.2, workingType='MARK_PRICE')
            client.futures_create_order(symbol=bot_symbol, side=tp_sl_side, type='STOP_MARKET', closePosition=True, stopPrice=sl_price, workingType='MARK_PRICE')
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position sl - ')
            print(e)
            failed = True

    # Exit everything if failed
    if failed:
        exitTrade(client, bot_symbol, tp_sl_side)
        return False

    return True

def exitTradeSpot(client : Client, symbol='BTCUSDT', bot_side = SIDE_BUY, current_balance=0):

    # Reset globals
    failed = True

    # Begin call for cancel all orders before open any trade
    while failed == True:
        try:
            # Cancel all orders on new signal
            client._delete('openOrders', True, data={'symbol': symbol})
            failed = False
        except BinanceAPIException as e:
            if('Unknown order sent' in e.message):
                failed = False
            else:
                print('Function exitTrade cancell all open orders - ')
                print(e.message)
                failed = True

    print('TP / SL canceled')

    failed = True

    print('Exiting trade because of failed request')

    if bot_side == SIDE_BUY:
        # Begin call for open trade
        while failed == True:
            try:
                # Cancel all orders on new signal
                client.create_order(symbol=symbol, side=bot_side, type=ORDER_TYPE_MARKET, quantity=current_balance)
                print('Position exited')
                failed = False
            except BinanceAPIException as e:
                if('Invalid quantity' in e.message):
                    failed = False
                else:
                    print('Function exitTrade - ')
                    print(e)
                    failed = True


def open_bot_position_spot(client : Client, bot_side=SIDE_BUY, stable_coin='USDT', crypto_asset='BTC', bot_starting_position_percent=0.6, bot_tp_percent=40, bot_sl_percent=30):

    action = 'Opening' if bot_side == SIDE_BUY else 'Closing'

    print(action + ' position for pair: ' + crypto_asset + '/' + stable_coin)

    bot_symbol = crypto_asset + stable_coin

    if bot_side==SIDE_SELL:
        exitTradeSpot(client, bot_symbol, SIDE_SELL)

    # Reset globals
    current_attempts = 0
    failed = True
    # Balance
    current_balance = 0

    #Based on what bot action we need balance for Crypto Asset or Stable Coin
    asset = stable_coin if bot_side == SIDE_BUY else crypto_asset

    # Decide bot Short/Long
    tp_sl_side = SIDE_SELL if bot_side == SIDE_BUY else SIDE_BUY

    print('Getting account balance in ' + asset + '...')

    # Begin call for get Account Balance
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Get acc balance
            current_balance = float(client.get_asset_balance(asset=asset)['free'])
            print('Current ' + asset + ' balance: ' + str(current_balance))
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position account balance - ' + str(e))
            failed = True

    # Exit everything if failed
    if failed:
        exitTradeSpot(client, bot_symbol, bot_side, current_balance)
        return False

        # Reset globals
    current_attempts = 0
    failed = True

    # Begin calculations such as
    # 1. Calculate quantity to open
    # 2. Calculate TP and SL

    # Begin call for get Price of the symbol
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Get Symbol Price
            symbol_price = float(client.get_symbol_ticker(symbol=bot_symbol)["price"])
            print('Symbol Price - ' + str(symbol_price))
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position price of symbol - ')
            print(e)
            failed = True

    # Exit everything if failed
    if failed:
        exitTradeSpot(client, bot_symbol, bot_side)
        return False

    if bot_side == SIDE_BUY:
        tp_price =  round_decimals_down(symbol_price + symbol_price * (bot_tp_percent / (1 * 100)), 2)
        sl_price = round_decimals_down(symbol_price - symbol_price * (bot_sl_percent / (1 * 100)), 2)
        print('Take Profit Price: ' + str(tp_price))
        print('Stop Loss Price: ' + str(sl_price))
    available_bot_balance_for_trade = round_decimals_down(current_balance * bot_starting_position_percent, 2)
    print('Available bot balance = ' + str(available_bot_balance_for_trade))

    bot_quantity = round_decimals_down(available_bot_balance_for_trade / symbol_price, 4)

    if bot_side == SIDE_SELL:
        bot_quantity = round_decimals_down(current_balance, 4)

    # bot_quantity = abs(bot_quantity + previous_position_amount)

    # Reset globals
    current_attempts = 0
    failed = True

    # Begin call for open trade
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Cancel all orders on new signal
            client.create_order(symbol=bot_symbol, side=bot_side, type=ORDER_TYPE_MARKET, quantity=bot_quantity)
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position open position - ')
            print(e)
            failed = True


    # Exit everything if failed
    if failed:
        exitTradeSpot(client, bot_symbol, bot_side)
        return False

        # Reset globals
    current_attempts = 0
    failed = True

    if bot_side == SIDE_SELL:
        # Reset globals
        failed = True

        # Begin call for cancel all orders before open any trade
        while failed == True:
            try:
                # Cancel all orders on new signal
                client._delete('openOrders', True, data={'symbol': bot_symbol})
                failed = False
            except BinanceAPIException as e:
                if('Unknown order sent' in e.message):
                    failed = False
                else:
                    print('Function exitTrade cancell all open orders - ')
                    print(e.message)
                    failed = True

        print('TP / SL canceled')
        return True

    # Begin call for tp open position
    while failed == True and current_attempts != max_attempts:
        # Increment counter
        current_attempts+=1
        try:
            # Take Profit
            client.create_oco_order(side=SIDE_SELL, symbol=bot_symbol, listClientOrderId='sltporder', quantity=bot_quantity, price=symbol_price*2, stopPrice=sl_price, stopLimitPrice=sl_price - 100, stopLimitTimeInForce='FOK')
            failed = False
        except BinanceAPIException as e:
            print('Function open_bot_position tp - ')
            print(e)
            failed = True

    # Exit everything if failed
    if failed:
        exitTradeSpot(client, bot_symbol, bot_side)
        return False

    return True

def entry(apikey='NULL', apisecret='NULL', data='NULL'):
    # Print current time so we know
    now = datetime.now()
    current_time = now.strftime("%H:%M:%S")
    print("Current Time =", current_time)

    # Authorize client
    clientKoko = Client(apikey, apisecret)

    data = json.loads(data)
#     if data['passphrase'] != config.WEBHOOK_PASSPHRASE:
#         return {
#             "code": "error",
#             "message": "Nice try, invalid passphrase"
#         }
#
    side = data['action'].upper()
    stable_coin = data['stable_coin']
    crypto_asset = data['crypto_asset']
#     # if open_bot_position(bot_side=side, bot_symbol='BTCUSDT', bot_leverage=1, bot_starting_position_percent=0.9, bot_tp_percent=0.8, bot_sl_percent=0.4):
#
    if open_bot_position_spot(client=clientKoko, bot_side=side, crypto_asset=crypto_asset, stable_coin=stable_coin, bot_starting_position_percent=0.9, bot_tp_percent=0.8, bot_sl_percent=0.2):
        return {
            "code": "success",
            "message": "Bot position opened"
        }
    else:
        return {
            "code": "error",
            "message": "Bot position failed to open"
        }

# @app.route('/vanka', methods=['POST'])
# def vanka():
#     # Print current time so we know
#     now = datetime.now()
#     current_time = now.strftime("%H:%M:%S")
#     print("Current Time =", current_time)
#
#     # Authorize client
#     clientVanka = Client(config.API_VANKA_KEY, config.API_VANKA_SECRET)
#
#     data = json.loads(request.data)
#
#     if data['passphrase'] != config.WEBHOOK_PASSPHRASE:
#         return {
#             "code": "error",
#             "message": "Nice try, invalid passphrase"
#         }
#
#     side = data['action'].upper()
#     stable_coin = data['stable_coin']
#     crypto_asset = data['crypto_asset']
#     # if open_bot_position(bot_side=side, bot_symbol='BTCUSDT', bot_leverage=1, bot_starting_position_percent=0.9, bot_tp_percent=0.8, bot_sl_percent=0.4):
#
#     if open_bot_position_spot(client=clientVanka, bot_side=side, crypto_asset=crypto_asset, stable_coin=stable_coin, bot_starting_position_percent=0.9, bot_tp_percent=0.8, bot_sl_percent=10.4):
#         return {
#             "code": "success",
#             "message": "Bot position opened"
#         }
#     else:
#         return {
#             "code": "error",
#             "message": "Bot position failed to open"
#         }

# @app.route('/chavo', methods=['POST'])
# def chavo():
#     # Print current time so we know
#     now = datetime.now()
#     current_time = now.strftime("%H:%M:%S")
#     print("Current Time =", current_time)
#
#     # Authorize client
#     clientChavo = Client(config.API_CHAVO_KEY, config.API_CHAVO_SECRET)
#
#     data = json.loads(request.data)
#
#     if data['passphrase'] != config.WEBHOOK_PASSPHRASE:
#         return {
#             "code": "error",
#             "message": "Nice try, invalid passphrase"
#         }
#
#     side = data['action'].upper()
#     stable_coin = data['stable_coin']
#     crypto_asset = data['crypto_asset']
#     # if open_bot_position(bot_side=side, bot_symbol='BTCUSDT', bot_leverage=1, bot_starting_position_percent=0.9, bot_tp_percent=0.8, bot_sl_percent=0.4):
#
#     if open_bot_position_spot(client=clientChavo, bot_side=side, crypto_asset=crypto_asset, stable_coin=stable_coin, bot_starting_position_percent=0.9, bot_tp_percent=0.8, bot_sl_percent=10.4):
#         return {
#             "code": "success",
#             "message": "Bot position opened"
#         }
#     else:
#         return {
#             "code": "error",
#             "message": "Bot position failed to open"
#         }
#
# @app.route('/futures', methods=['POST'])
# def futures():
#     # Print current time so we know
#     now = datetime.now()
#     current_time = now.strftime("%H:%M:%S")
#     print("Current Time =", current_time)
#
#     # Authorize client
#     clientKoko = Client(config.API_KEY, config.API_SECRET)
#
#     data = json.loads(request.data)
#
#     if data['passphrase'] != config.WEBHOOK_PASSPHRASE:
#         return {
#             "code": "error",
#             "message": "Nice try, invalid passphrase"
#         }
#
#     side = data['action'].upper()
#     stable_coin = data['stable_coin']
#     crypto_asset = data['crypto_asset']
#     if open_bot_position(client=clientKoko, bot_side=side, crypto_asset=crypto_asset, stable_coin=stable_coin, bot_leverage=7, bot_starting_position_percent=0.9, bot_tp_percent=300, bot_sl_percent=20):
#       return {
#             "code": "success",
#             "message": "Bot position opened"
#         }
#     else:
#         return {
#             "code": "error",
#             "message": "Bot position failed to open"
#         }

# @app.route('/iliana', methods=['POST'])
# def iliana():
#     # Print current time so we know
#     now = datetime.now()
#     current_time = now.strftime("%H:%M:%S")
#     print("Current Time =", current_time)
#
#     # Authorize client
#     clientIliana = Client(config.API_ILIANA_KEY, config.API_ILIANA_SECRET)
#
#     data = json.loads(request.data)
#
#     if data['passphrase'] != config.WEBHOOK_PASSPHRASE:
#         return {
#             "code": "error",
#             "message": "Nice try, invalid passphrase"
#         }
#
#     side = data['action'].upper()
#     stable_coin = data['stable_coin']
#     crypto_asset = data['crypto_asset']
#     if open_bot_position(client=clientIliana, bot_side=side, crypto_asset=crypto_asset, stable_coin=stable_coin, bot_leverage=1, bot_starting_position_percent=0.9, bot_tp_percent=0.2, bot_sl_percent=10):
#       return {
#             "code": "success",
#             "message": "Bot position opened"
#         }
#     else:
#         return {
#             "code": "error",
#             "message": "Bot position failed to open"
#         }
entry(client_key, client_secret, data)
