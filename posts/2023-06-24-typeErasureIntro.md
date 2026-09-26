---
title: "Type Erasure"
date: 2023-07-09T23:53:10+05:30
description: An interesting design pattern!
tags: [CPP, virtual function, type erasure, programming]
---

Recently I came across a programming pattern that has been there for a while called **Type Erasure Design Pattern**. It has been very useful if abstracting types across multiple layers. I found it very useful when designing a graphics engine that we are creating at work.

## What is Type Erasure? 

As the name indicates, it erases or hides the *type information* completely from one part of the system to the other. The idea is quite easy to grasp if we have a basic understanding of [virtual functions](/tags/virtual-function/). For ease of describing what it is, I will build it on the examples used in the previous chapters, particularly from this [post](/posts/2023-01-26-virtualfunc2/) which is here again.


```c++
class Actor {
    virtual void render() = 0;
    virtual void update() = 0;
};

class Player : public Actor {
    virtual void render() override {
        printf("Player render\n");
        // render player stuff
    }

    virtual void update() override {
        printf("Player update\n");
        // update player stuff
    }

    // local/unique members and methods
};

class Monster : public Actor {
    virtual void render() override {
        printf("Monster render\n");
        // render monster stuff
    }

    virtual void update() override {
        printf("Monster update\n");
        // update monster stuff
    }
    
    // local/unique members and methods
};
```

By the end of the tutorial, we expanded the idea over various types. Here, all the types implicitly or explicitly are derived from the same type called `Actor`. In a large-scale system, there is no guarantee that this trait can be maintained. Further, if it should work, we should maintain a `trait` and make sure it is implicitly or explicitly exposed to various components and across all the components in the system. This can go out of control in no time. Whenever there is a new component that should be exposed to different types, it will be a PITA to design. 

The **Type Erasure Design Pattern** is designed particularly to solve this messy design issue that "may" arise when using inheritance and polymorphism extensively. Let's look at the solution in a step-by-step approach.

## Functional Programming and Templates
The most widely used system programming language C++ encourages us to go full **Object Oriented** but doesn't particularly talk much about the benefits of functional programming. Most importantly, C++ doesn't prevent us from doing it either. 

As discussed earlier, if the concrete types we are working on are guaranteed to be derived from the same base class, Inheritance and Polymorphism are good solutions. If, for some reason, we are unable to derive them from the same base type, then the inheritance and polymorphism may become messy over time. Thus, we can make use of the functional programming style to overcome the issue at hand and our solution will look something like this.

```c++
template <typename T>
void render(T* actor){
    actor->render();
}

template <typename T>
void update(T* actor){
    actor->update();
}

class Player {
    void render() {
        printf("Player render\n");
        // render player stuff
    }

    void update() {
        printf("Player update\n");
        // update player stuff
    }
};

class Monster {
    void render() {
        printf("Monster render\n");
        // render monster stuff
    }

    void update() {
        printf("Monster update\n");
        // update monster stuff
    }
};
```

This function can be called anywhere with any parameters, as long as the parameters passed defines the functions `update` and `render`. As you observe, there is no need for a class called `Actor`. The need for `Monster` and `Player` to share the same common base type is also unnecessary, thus the need for the keywords `virtual` and `override` is also gone(only for now).

These methods `update` and `render` works on `Monster` and `Player` because of the keyword `template` which plays a key role at compile time. Whenever a type is invoked on a templated function, the compiler generates an overloaded function of the templated function for that invocation. To make it clear, if we make sure that the program will compile if, in the templated method, the template type `T` is replaced by `Monster` or `Player`, the template invocation is valid.

Suppose, if there is a call like this somewhere in the program,
```c++
Player p;
render(&p);
```
The compiler will compile the function `render(T* actor)` by kinda replacing the `T` with `Player`. In layman's terms, it may look something like this.
```c++
void render<Player>(Player *actor){
    actor->render();
}
```
## Drawbacks of templated functions
There are no drawbacks in the pattern itself. Using `render(T* actor)` and `update(T* actor)` gives us an advantage by letting us get rid of sharing the same base class and introduces a more pressing problem. We lost the ability to shove it all under the same type as we did before! For instance, we can no longer do this. 
```c++
Actor* actors [] = {
        new Player(),
        new Monster(),
        new Cyclops(),
        new Monster()
    };
```
Now, there is no type called Actor and there is no way to store anything without knowing its type.

Another drawback is, the caller should be aware of all the concrete types to begin with. This is more of an impossible-to-solve problem if you think about it. There is no way to expose all the concrete types to any system that may or may not use it and it is much more difficult when new types are introduced along the way.

If this should work without exposing the types, the caller must also be templated. This too, will quickly get out of hand and make it difficult to organize the program.

## Wrapping it up
To fix this problem, let's build a new interface! Here we are working on ways to implement `render` and `update` functionalities. We are looking for ways to implement the `Actor` type. Thus we reintroduce the same.

```c++
class Actor {
    public:
    virtual void render() = 0;
    virtual void update() = 0;
};
```
For the `Player` and `Monster` to work along with this `Actor`, we have to wrap them over a wrapper class like this.

```c++
class PActorWrapper : public Actor {
    private:
    Player *m_player;
    
    public:
    PActorWrapper(Player* player)
    :m_player(player){}

    virtual void render() { m_player->render(); }
    virtual void update() { m_player->update(); }
};

class MActorWrapper : public Actor {
    private:
    Monster *m_monster;
    
    public:
    MActorWrapper(Monster* monster)
    :m_monster(monster){}

    virtual void render() { m_monster->render(); }
    virtual void update() { m_monster->update(); }
};
```
Now, we can shove it all under the same type. Here, we just have to be aware of what all falls under this category. We should know the types in our game that should be `update`d and `render`ed. Thus we will end up having a list of types that should be `update`d and `render`ed without the need to be aware of their underlying types at all times.

```c++
ActorWrapper* actors [] = {
        new PActorWrapper(new Player()),
        new MActorWrapper(new Monster()),
        new CActorWrapper(new Cyclops()),
        new MActorWrapper(new Monster())
    };
```
Even now we have a bigger issue staring right at us!
You do notice the new type called `Cyclops` there, right? It means we should also write a wrapper for the type `Cyclops` and any other type that is introduced further in the game. This introduces a much bigger problem of adding and removing numerous wrappers in our code.

But we already have a solution to avoid this new problem thrown at us. We can make use of `template` programming. Instead of writing a wrapper over individual types, we can `template` the wrapper like this.

```c++
template<typename T>
class ActorWrapper : public Actor {
    private:
    T *m_actor;
    
    public:
    ActorWrapper(T* actor)
    :m_actor(actor){}

    virtual void render() { m_actor->render(); }
    virtual void update() { m_actor->update(); }
};
```

Now, we have a single type called `ActorWrapper` and let the compiler generate the derived classes `ActorWrapper<Player>`, `ActorWrapper<Monster>` and `ActorWrapper<Cyclops>` for us. This can extend beyond these `Player` `Monster` and `Cyclops`. Any number of desired `Actor`s can be created. We have to make sure that they define `render` and `update` functions in those classes.

This will let us do something like this.

```c++
ActorWrapper* actors [] = {
        new ActorWrapper(new Player()),
        new ActorWrapper(new Monster()),
        new ActorWrapper(new Cyclops()),
        new ActorWrapper(new Monster())
    };
```
The idiom **Type Erasure** gets its meaning from here because the crux of the idea is to implement an `Actor` class that unifies our other types like `Player`, `Monster` etc. We have unified all the `Actors` in our game by hiding them behind a custom interface called the `ActorWrapper` which just forwards the calls to the underlying types.

## Clean up!
So far what we have discussed is the basic idea behind the concept of type erasure. As iterated, the type of the concrete class is erased by hiding it under a **wrapper** class that implements the **concept** (here the concept is an `Actor`). But the code can look messy when we create the *objects* of the wrapper class which will be like this `new ActorWrapper(new Player())` or sometimes it may even look messier like this `new ActorWrapper<Player>(new Player())`. What is left is to hide all the messiness under a class to make it look cleaner outside, i.e. when it is used in other parts of the game.

In naming them, the class names ending with **model** and **concept** are quite standard ones. The class named `Actor` in the most recent [code block](#wrapping-it-up) captures the *concept* of an *Actor*, such as to `render` and to be able to `update` will be named as `ActorConcept`. The wrapper that implements the functions and forwards the call to the concrete types can be named as `ActorWrapper` or as `ActorModel`. Here we choose to go with the name `ActorModel` a widely accepted naming convention.

```c++
class Actor {
    private:
    
    class ActorConcept {
        public:
        virtual void render() = 0;
        virtual void update() = 0;
    };

    template<typename T>
    class ActorModel : public ActorConcept {
        private:
        T *m_actor;
        
        public:
        ActorModel(T* actor)
        :m_actor(actor){}

        virtual void render() { m_actor->render(); }
        virtual void update() { m_actor->update(); }
    };

    std::vector<ActorModel*> m_actors;

    public:
    template<typename T>
    void addActor(T* actor){
        m_actors.push_back(new ActorModel(actor));
    }

    void update(){
        for(auto actor: m_actors)
            actor->update();
    }
    
    void render(){
        for(auto actor: m_actors)
            actor->render();
    }
};
```
Once this `Actor` is implemented, in our code it can be used as follows.

```c++
    Player *p = new Player();
    Monster *m = new Monster();
    // continue to create other actors.

    Actor actors;
    actors.addActor(p);
    actors.addActor(m);
    //continue to add other actors.

    actors.update();
    //Whenever we want to update them
    
    actors.render();
    //Whenever we want to render them
```
Similar type erased **concepts** can be built for other requirements and the external system need not be aware of the underlying concrete types. This gives us greater flexibility in building large-scale systems with a lot of interdependencies. 

## More information?
* For more info on C++ type erasure take a look at Andrzej’s series on type erasure ([Part 1](https://akrzemi1.wordpress.com/2013/11/18/type-erasure-part-i/), [Part 2](https://akrzemi1.wordpress.com/2013/12/06/type-erasure-part-ii/), [Part 3](https://akrzemi1.wordpress.com/2013/12/11/type-erasure-part-iii/), [Part 4](https://akrzemi1.wordpress.com/2014/01/13/type-erasure-part-iv/)). 
* There is an excellent talk on this concept by **Klaus Iglberger** available on [youtube](https://www.youtube.com/watch?v=qn6OqefuH08).
<!---
# Pure Functions
Many of us are already aware of free functions. Most of us use Pure Functions without knowing that they are Pure Functions. It can be loosely defined as *a function that only looks at the parameters passed into it, and returns one or more computed values based on the parameters*. It is one of the first things taught in class like **Programming 101**. Below is a very common pure function that almost everyone is aware of.

```c++
int add(int a, int b) {
   return a + b; 
}
```
The elegance of a pure function is, it leaves no *footprint* in the system. It does not mutate the input parameters, it maintains no internal state, and it doesn't update any global state as well. There is a good article by [John Carmack](https://en.wikipedia.org/wiki/John_Carmack) on functional programming [here](http://sevangelatos.com/john-carmack-on/) where he discussed in length about pure functions.

-->

I hope this post on Type Erasure was useful. Have any questions? Did I make any mistakes? Let me know on [Twitter](https://twitter.com/madptr) or [Mastodon](https://mastodon.gamedev.place/@madptr)!