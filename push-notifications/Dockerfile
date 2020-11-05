FROM node:lts-slim

RUN mkdir -p /usr/src/
WORKDIR /usr/src/
COPY . /usr/src/

RUN yarn install

EXPOSE 8000

CMD ["npm", "run", "start:docker"]
#CMD ["npm", "run", "start:watch"]
