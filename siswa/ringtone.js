var ringToneData = 'data:audio/mp3;base64,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qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqv/zKGTMAAABpAAAAAAAAANIAAAAAKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqg==';
var ringtone = new Audio(ringToneData);