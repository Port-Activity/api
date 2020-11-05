import express from 'express';
import fetch from 'node-fetch';
import { Expo } from 'expo-server-sdk';
import redis from 'redis';

const PUSH_NOTIFICATIONS_PORT = process.env.PUSH_NOTIFICATIONS_PORT || 8000;
const PUSH_NOTIFICATIONS_APIKEY = process.env.PUSH_NOTIFICATIONS_APIKEY || null;
const API_URL = process.env.API_URL || 'http://api';
const API_ENDPOINT = process.env.API_ENDPOINT || 'push-notification-tokens';
const AINO_URL = process.env.AINO_URL || 'https://data.aino.io/rest/v2.0/transaction';
const AINO_APIKEY = process.env.AINO_APIKEY || null;

const EXPO_NAME = 'Expo push notifications server';
const NODE_NAME = 'API push notifications handler';
const API_NAME = 'PortActivity';

const app = express();
const expo = new Expo();

const client = redis.createClient({
  host: 'redis',
});

// set port
app.set('port', PUSH_NOTIFICATIONS_PORT);

app.use(express.json());

// Add GET /health-check express route
app.get('/health-check', (req, res) => {
  res.status(200).send('OK');
});

/*
app.post('/send', (req, res) => {
  handlePushTokens(req.body);
  console.log(`Received message, with title: ${req.body.title}`);
  res.send(`Received message, with title: ${req.body.title}`);
});
*/

app.listen(PUSH_NOTIFICATIONS_PORT, () => {
  console.log(`Server Online on Port ${PUSH_NOTIFICATIONS_PORT}`);
});

const blpop = () => {
  client.blpop('push-notification', 0, (err, data) => {
    //console.log(`brpop from: ${data[0]}`);
    if (data && data.length === 2 && data[0] === 'push-notification') {
      const item = JSON.parse(data[1]);
      try {
        if (item) {
          handlePushTokens(item);
        }
      } catch (error) {
        console.log('Error handling notification: ', error);
      }
      process.nextTick(blpop);
      //setTimeout(brpop, 1000);
    }
  });
};

blpop();

const handlePushTokens = ({ body, customBodies, data, id, license_plates, title, tokens, type, vessel_id }) => {
  if (!tokens && !tokens.length) {
    return;
  }
  const flowId = id;
  // Create the messages that you want to send to clents
  let messages = [];
  for (let pushToken of tokens) {
    // Each push token looks like ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]

    // Check that all your push tokens appear to be valid Expo push tokens
    if (!Expo.isExpoPushToken(pushToken)) {
      console.log(`Push token ${pushToken} is not a valid Expo push token`);
      continue;
    }

    // Construct a message (see https://docs.expo.io/versions/latest/guides/push-notifications)
    let message = {
      to: pushToken,
      sound: 'default',
      title: title,
      body: customBodies && customBodies[pushToken] ? customBodies[pushToken] : body,
      data: {
        id: id,
        type: type,
      },
      channelId: 'notification', // Required with android, currently we support only notification channel
      priority: 'high',
    };
    if (data) {
      message.data['data'] = data;
    }
    if (license_plates) {
      message.data['license_plates'] = license_plates;
    }
    if (vessel_id) {
      message.data['vessel_id'] = vessel_id;
    }
    messages.push(message);
  }

  //console.log('Messages: ', messages);
  if (!messages && !messages.length) {
    return;
  }

  // The Expo push notification service accepts batches of notifications so
  // that you don't need to send 1000 requests to send 1000 notifications. We
  // recommend you batch your notifications to reduce the number of requests
  // and to compress them (notifications with similar content will get
  // compressed).
  let chunks = expo.chunkPushNotifications(messages);
  let tickets = [];
  (async () => {
    // Send the chunks to the Expo push notification service. There are
    // different strategies you could use. A simple one is to send one chunk at a
    // time, which nicely spreads the load out over time:
    for (let chunk of chunks) {
      try {
        sendMessagesToAino('success', 'sendPushNotificationsAsync', 'Sending push notifications', flowId, chunk);
        let ticketChunk = await expo.sendPushNotificationsAsync(chunk);
        const ticketsWithTokens = ticketChunk.map((ticket, index) => {
          if (chunk[index]) {
            ticket.token = chunk[index].to;
          }
          return ticket;
        });
        console.log('Response tickets with tokens: ', ticketsWithTokens);
        tickets.push(...ticketsWithTokens);
        sendTicketsToAino(
          'success',
          'sendPushNotificationsAsync',
          'Push notifications sent, tickets received',
          flowId,
          tickets
        );
        // NOTE: If a ticket contains an error code in ticket.details.error, you
        // must handle it appropriately. The error codes are listed in the Expo
        // documentation:
        // https://docs.expo.io/versions/latest/guides/push-notifications#response-format
      } catch (error) {
        console.log(error);
        sendMessagesToAino('failure', 'sendPushNotificationsAsync', 'Push notifications send failed', flowId, chunk);
      }
    }
    // Check receipts one hour after sending
    if (tickets.length) {
      setTimeout(() => {
        checkReceipts(tickets, flowId);
      }, 3600000);
    }
  })();
};

const checkReceipts = (tickets, flowId) => {
  // Later, after the Expo push notification service has delivered the
  // notifications to Apple or Google (usually quickly, but allow the the service
  // up to 30 minutes when under load), a "receipt" for each notification is
  // created. The receipts will be available for at least a day; stale receipts
  // are deleted.
  //
  // The ID of each receipt is sent back in the response "ticket" for each
  // notification. In summary, sending a notification produces a ticket, which
  // contains a receipt ID you later use to get the receipt.
  //
  // The receipts may contain error codes to which you must respond. In
  // particular, Apple or Google may block apps that continue to send
  // notifications to devices that have blocked notifications or have uninstalled
  // your app. Expo does not control this policy and sends back the feedback from
  // Apple and Google so you can handle it appropriately.
  let receiptIds = [];
  for (let ticket of tickets) {
    // NOTE: Not all tickets have IDs; for example, tickets for notifications
    // that could not be enqueued will have error information and no receipt ID.
    if (ticket.id) {
      receiptIds.push(ticket.id);
    }
  }

  let receiptIdChunks = expo.chunkPushNotificationReceiptIds(receiptIds);
  (async () => {
    // Like sending notifications, there are different strategies you could use
    // to retrieve batches of receipts from the Expo service.
    for (let chunk of receiptIdChunks) {
      try {
        let successReceipts = [];
        let failureReceipts = [];
        let staleTokens = [];
        sendReceiptIdsToAino('success', 'checkReceipts', 'Getting receipts', flowId, chunk);
        let receipts = await expo.getPushNotificationReceiptsAsync(chunk);
        if (!receipts || !Object.entries(receipts).length) {
          // Receipts are not yet available, try later
          // TODO
          console.log('Receipts not yet available');
          sendReceiptIdsToAino('failure', 'checkReceipts', 'Receipts not yet available', flowId, chunk);
          return;
        }
        console.log('Push receipts received: ', receipts);
        // The receipts specify whether Apple or Google successfully received the
        // notification and information about an error, if one occurred.
        for (let [id, receipt] of Object.entries(receipts)) {
          if (receipt.status === 'ok') {
            successReceipts.push({ id: id, ...receipt });
            continue;
          } else if (receipt.status === 'error') {
            console.log(`There was an error sending a notification: ${receipt.message}`);
            if (receipt.details && receipt.details.error) {
              // The error codes are listed in the Expo documentation:
              // https://docs.expo.io/versions/latest/guides/push-notifications#response-format
              // You must handle the errors appropriately.
              console.log(`The error code is ${receipt.details.error}`);
              if (receipt.details.error === 'DeviceNotRegistered') {
                const ticket = tickets.find(ticket => ticket.id === id);
                if (ticket && ticket.token) {
                  staleTokens.push(ticket.token);
                }
              } else {
                failureReceipts.push({ id: id, ...receipt });
              }
            }
          }
        }
        if (staleTokens.length) {
          await removeStaleTokens(staleTokens, flowId);
        }
        if (successReceipts.length) {
          sendReceiptsToAino('success', 'checkReceipts', 'Successful receipts', flowId, successReceipts);
        }
        if (failureReceipts.length) {
          sendReceiptsToAino('failure', 'checkReceipts', 'Failed receipts', flowId, failureReceipts);
        }
      } catch (error) {
        // TODO: Send notfiticaion to Aino.io
        console.log(error);
        sendReceiptIdsToAino('failure', 'checkReceipts', error, flowId, chunk);
      }
    }
  })();
};

const removeStaleTokens = async (tokens, flowId) => {
  if (!tokens || !tokens.length) {
    return;
  }
  if (!PUSH_NOTIFICATIONS_APIKEY) {
    return sendStaleTokensToAino('failure', 'removeStaleTokens', 'Push notifications apikey missing', flowId, tokens);
  }
  const url = `${API_URL}/${API_ENDPOINT}`;
  let headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    Authorization: `ApiKey ${PUSH_NOTIFICATIONS_APIKEY}`,
  };
  console.log('Removing stale tokens: ', tokens);
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({
        tokens: tokens,
      }),
    });
    if (response.status === 200) {
      const resp = await response.json();
      if (resp && resp.error) {
        console.warn('Error: ', resp.error);
        sendStaleTokensToAino('failure', 'removeStaleTokens', resp.error, flowId, tokens);
      } else {
        console.log('Stale tokens removed');
        sendStaleTokensToAino('success', 'removeStaleTokens', 'Stale tokens removed', flowId, tokens);
      }
    } else {
      console.log(`${response.url} failed, ok:${response.ok} status:${response.status} text:${response.statusText}`);
      sendStaleTokensToAino('failure', 'removeStaleTokens', response.statusText, flowId, tokens);
    }
  } catch (error) {
    console.warn(error);
    sendStaleTokensToAino('failure', 'removeStaleTokens', error, flowId, tokens);
  }
};

const sendMessagesToAino = (status, operation, message, flowId, messages) => {
  if (!messages.length) {
    return;
  }
  const transactions = [
    {
      from: NODE_NAME,
      to: EXPO_NAME,
      status: status,
      timestamp: new Date().getTime(),
      message: message,
      operation: operation,
      payloadType: 'Push notifictions',
      ids: [{ idType: 'Push notification tokens', values: messages.map(msg => msg.to) }],
      metadata: [
        { name: 'title', value: messages[0].title },
        { name: 'body', value: messages[0].body },
        { name: 'channelId', value: messages[0].channelId },
      ],
      flowId: flowId,
    },
  ];
  return sendAinoTransaction(transactions);
};

const sendTicketsToAino = (status, operation, message, flowId, tickets) => {
  const transactions = tickets.map(ticket => {
    let metadata = [
      { name: 'status', value: ticket.status },
      { name: 'token', value: ticket.token },
    ];
    if (ticket.message) {
      metadata.push({ name: 'message', value: ticket.message });
    }
    if (ticket.code) {
      metadata.push({ name: 'code', value: ticket.code });
    }
    if (ticket.details) {
      metadata = metadata.concat(
        Object.entries(ticket.details).map(([key, value]) => {
          return { name: key, value: value };
        })
      );
    }
    return {
      from: EXPO_NAME,
      to: NODE_NAME,
      status: status,
      timestamp: new Date().getTime(),
      message: message,
      operation: operation,
      payloadType: 'Tickets',
      ids: [{ idType: 'Ticket ids', values: [ticket.id] }],
      metadata: metadata,
      flowId: flowId,
    };
  });
  return sendAinoTransaction(transactions);
};

const sendReceiptIdsToAino = (status, operation, message, flowId, receiptIds) => {
  const transactions = [
    {
      from: EXPO_NAME,
      to: NODE_NAME,
      status: status,
      timestamp: new Date().getTime(),
      message: message,
      operation: operation,
      payloadType: 'Receipt ids',
      ids: [{ idType: 'Receipt ids', values: receiptIds }],
      flowId: flowId,
    },
  ];
  return sendAinoTransaction(transactions);
};

const sendReceiptsToAino = (status, operation, message, flowId, receipts) => {
  const transactions = receipts.map(receipt => {
    let metadata = [{ name: 'status', value: receipt.status }];
    if (receipt.message) {
      metadata.push({ name: 'message', value: receipt.message });
    }
    if (receipt.code) {
      metadata.push({ name: 'code', value: receipt.code });
    }
    if (receipt.details) {
      metadata = metadata.concat(
        Object.entries(receipt.details).map(([key, value]) => {
          return { name: key, value: value };
        })
      );
    }
    return {
      from: NODE_NAME,
      to: EXPO_NAME,
      status: status,
      timestamp: new Date().getTime(),
      message: message,
      operation: operation,
      payloadType: 'Receipts',
      ids: [{ idType: 'Receipt ids', values: [receipt.id] }],
      metadata: metadata,
      flowId: flowId,
    };
  });
  return sendAinoTransaction(transactions);
};

const sendStaleTokensToAino = (status, operation, message, flowId, tokens) => {
  const transactions = [
    {
      from: NODE_NAME,
      to: API_NAME,
      status: status,
      timestamp: new Date().getTime(),
      message: message,
      operation: operation,
      payloadType: 'Push notificatin tokens',
      ids: [{ idType: 'Tokens', values: tokens }],
      flowId: flowId,
    },
  ];
  return sendAinoTransaction(transactions);
};

const sendAinoTransaction = async transactions => {
  if (!AINO_APIKEY) {
    console.log('Error: Aino apikey missing!');
    /*
    transactions.forEach(({ from, to, status, timestamp, message, operation, payloadType, ids, metadata, flowId }) => {
      console.log(from, to, status, timestamp, message, operation, payloadType, ids, metadata, flowId);
    });*/
    return;
  }
  try {
    const response = await fetch(AINO_URL, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: `apikey ${AINO_APIKEY}`,
      },
      body: JSON.stringify({
        transactions: transactions,
      }),
    });
    if (response.status >= 200 && response.status < 300) {
      // Aino.io returns 202
      const resp = await response.json();
      if (resp && resp.error) {
        console.warn('Error: ', resp.error);
      } else {
        console.log('Aino transaction sent');
      }
    } else {
      console.log(`${response.url} failed, ok:${response.ok} status:${response.status} text:${response.statusText}`);
    }
  } catch (error) {
    console.warn(error);
  }
};
