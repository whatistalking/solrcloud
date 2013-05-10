#!/usr/bin/evn python

import zmq
import sys
import msgpack
import argparse

parser = argparse.ArgumentParser(" access_log enter ods publisher")

parser.add_argument('-H' , '--host', help="connect host", required=True)
parser.add_argument('-T' , '--type', help="log type", required=True)
parser.add_argument('-P' , '--port', help="connect port", required=True, type=int)

args = parser.parse_args()

context = zmq.Context()
pusher = context.socket(zmq.PUSH)
pusher.setsockopt(zmq.HWM, 20000)
pusher.connect('tcp://' + args.host + ':' + str(args.port))
type = args.type

for i in sys.stdin:
    msg = {"type":type , "body":i }
    pusher.send(msgpack.packb(msg))
